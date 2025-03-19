<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ProjectsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Projects;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken; 
use Psr\Log\LoggerInterface;
use App\Form\ProjectType;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\Notifications;
use App\Entity\Users;
use App\Entity\Votes;
use App\Repository\UsersRepository;
use App\Repository\VotesRepository;
use Cloudinary\Cloudinary;
use App\Service\DynamoDBService;


class ProjectsController extends AbstractController
{
    #[Route('/projects', name: 'app_projects')]
    
    public function index(): Response
    {

        return $this->render('projects/index.html.twig', [
            'controller_name' => 'ProjectsController',
        ]);
    }


        

    /* Route qui affiche le project */

    #[Route('/projects/{projects}', name: 'app_projects_project')]
    
    public function projectOfProject(string $projects, ProjectsRepository $repo, CommentsRepository $commentsRepo, Security $security, EntityManagerInterface $entityManager, DynamoDBService $dynamoDBService): Response
    {

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        

        
         // Trouver le projet en fonction du slug
        $project = $repo->findProjectBySlug($projects);
        

        // 🔹 Vérifier si le projet existe bien
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }
    

        // 🔹 Récupérer et vérifier les images
        $images = $project->getImages();
    

        // 🔹 Récupérer et vérifier les vidéos
        $videos = $project->getVideos();

        // Récupérer les commentaires pour ce projet
        $comments = $commentsRepo->findBy(['project_id' => $project->getId()], ['created_at' => 'DESC']);
    
        // Récupérer les commentaires depuis DynamoDB
        /* $comments = $dynamoDBService->getCommentsByProject($project->getId()); */

        
          // Récupérer l'utilisateur qui a posté le projet en utilisant son `userId`
    $projectOwner = $entityManager->getRepository(Users::class)->find($project->getUserId());

    return $this->render('projects/page-project.html.twig', [
        'controller_name' => 'ProjectsController',
        'project' => $project,
        'images' => $images,
        'videos' => $videos,
        'comments' => $comments,
        'user' => $user, // Passer l'utilisateur connecté
        'projectOwner' => $projectOwner,
    ]);
    }







   /* Page création de projects */

        #[Route('/project/create', name: 'app_projects_create', methods: ['GET', 'POST'])]
        #[IsGranted('ROLE_USER')]
        public function createProject(Request $request, EntityManagerInterface $manager, CategoriesRepository $categoryRepo, Security $security, SluggerInterface $slugger, ProjectsRepository $projectRepo): Response {
        
             // Configuration Cloudinary
             $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                    'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
                    'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
                ]
            ]);

            $project = new Projects();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'utilisateur connecté
            $user = $security->getUser();
            if (!$user) {
                return $this->redirectToRoute('app_login');
            }

            // Assigner l'ID utilisateur et la catégorie
            $project->setUserId($user->getId());
            /* $categoryId = (int) $request->request->get('categories');
            $project->setCategoryId($categoryId); */

            // ✅ Générer un slug unique
            $baseSlug = $slugger->slug($project->getTitle())->lower();
            $slug = $baseSlug;
            $counter = 1;

            while ($projectRepo->findOneBy(['slug' => $slug])) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $project->setSlug($slug);

            // ✅ Assigner la date de création
            $project->setCreatedAt(new \DateTimeImmutable());

            // ✅ Assigner la date de mise à jour
            $project->setUpdatedAt(new \DateTime());

            // Gérer les fichiers (images, vidéos)
            $images = $request->files->get('images');
            $videos = $request->files->get('videos');

         // Upload des images sur Cloudinary
         if ($images) {
            $imagePaths = [];
            foreach ($images as $image) {
                if ($image) {
                    try {
                        $uploadResult = $cloudinary->uploadApi()->upload($image->getPathname(), [
                            'folder' => 'projects/images',  // Spécifiez le dossier si nécessaire
                            'public_id' => uniqid() // Optionnel : un identifiant unique pour chaque image
                        ]);
                        $imagePaths[] = $uploadResult['secure_url'];  // Stocke l'URL de l'image
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Error uploading image to Cloudinary');
                    }
                }
            }
            $project->setImages($imagePaths);
        }

        // Upload des vidéos sur Cloudinary
        if ($videos) {
            $videoPaths = [];
            foreach ($videos as $video) {
                if ($video) {
                    try {
                        $uploadResult = $cloudinary->uploadApi()->upload($video->getPathname(), [
                            'folder' => 'projects/videos',  // Spécifiez le dossier si nécessaire
                            'resource_type' => 'video',  // Déclarez que c'est une vidéo
                            'public_id' => uniqid() // Optionnel : un identifiant unique pour chaque vidéo
                        ]);
                        $videoPaths[] = $uploadResult['secure_url'];  // Stocke l'URL de la vidéo
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Error uploading video to Cloudinary');
                    }
                }
            }
            $project->setVideos($videoPaths);
        }
            // Sauvegarde en base
            $manager->persist($project);
            $manager->flush();

            // ✅ Notification pour le créateur du projet
            $notification = new Notifications();
            $notification->setUser($user);
            $notification->setType('creation');
            $notification->setMessage("Your project '{$project->getTitle()}' has been created!");
            $notification->setCreatedAt(new \DateTime());
            $notification->setSeen(false);
            $manager->persist($notification);
            $manager->flush();

            return $this->redirectToRoute('app_projects');
        }

        $categories = $categoryRepo->findAll();
        
        
        return $this->render('projects/create-project.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories,
        ]);
        }

        



    /* Route pour découvrir un project */
    #[Route('/project/discovert', name: 'app_project_discovert')]
    public function projectDiscovert(ProjectsRepository $repo): Response
    {

         // Récupérer 10 projets au hasard
    $projects = $repo->findRandomProjects(10);

    // Renvoyer à la vue discovert.html.twig
    return $this->render('projects/discovert.html.twig', [
        'projects' => $projects,
    ]);

    
        
    
    }
    





    /* Route pour afficher le classement des project les plus liker(votes) */
    #[Route('/project/classement', name: 'app_project_classement')]
    
        public function classement(ProjectsRepository $projectRepo ,  VotesRepository $votesRepo, EntityManagerInterface $manager, UsersRepository $userRepo): Response
    {
    $topProjects = $projectRepo->findTopProjects(10); // Récupérer les 10 projets les plus votés
    


     // ✅ Notifier les utilisateurs dont le projet est dans le top
     foreach ($topProjects as $project) {
        $ownerId = $project['user_id']; // Récupère l'ID de l'utilisateur (si 'user_id' existe)
        $owner = $userRepo->find($ownerId);
        if ($owner) {
            // Vérifier si l'utilisateur n'a pas déjà reçu une notification récente
            $existingNotification = $manager->getRepository(Notifications::class)->findOneBy([
                'user' => $owner,
                'type' => 'ranking',
                'message' => "Your project '{$project['title']}' is in the ranking of the most liked!",

            ]);

            if (!$existingNotification) {
                $notification = new Notifications();
                $notification->setUser($owner);
                $notification->setType('ranking');
                $notification->setMessage("Your project '{$project['title']}' is in the ranking of the most liked!");
                $notification->setCreatedAt(new \DateTime());
                $notification->setSeen(false);
                $manager->persist($notification);
            }
        }
    }

    $manager->flush();

    return $this->render('projects/classement.html.twig', [
        'topProjects' => $topProjects
    ]);
}




    /* Route pour supprimer un project */

    #[Route('/project/delete/{id}', name: 'app_projects_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteProject(int $id, ProjectsRepository $repo, EntityManagerInterface $manager, Security $security): Response
    {
    // 🔹 Trouver le projet par son ID
    $project = $repo->find($id);

    // 🔹 Vérifier si le projet existe
    if (!$project) {
        throw $this->createNotFoundException('Projet introuvable.');
    }

    // 🔹 Vérifier si l'utilisateur connecté est bien l'auteur du projet
            if ($security->getUser()->getId() !== $project->getUserId()) {
                $this->addFlash('error', 'You can only delete your own projects.');
                return $this->redirectToRoute('app_projects');
            }

            // 🔹 Supprimer les fichiers liés (images et vidéos)
            $uploadsDir = $this->getParameter('uploads_directory');

            // Vérifier si 'images' n'est pas null et est un tableau
            if ($project->getImages() && is_array($project->getImages())) {
                foreach ($project->getImages() as $image) {
                    $imagePath = $uploadsDir . '/' . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath); // Supprime l'image
                    }
                }
            }

            // Vérifier si 'videos' n'est pas null et est un tableau
            if ($project->getVideos() && is_array($project->getVideos())) {
                foreach ($project->getVideos() as $video) {
                    $videoPath = $uploadsDir . '/' . $video;
                    if (file_exists($videoPath)) {
                        unlink($videoPath); // Supprime la vidéo
                    }
                }
            }

                // Trouver toutes les entrées de la table 'votes' qui se réfèrent à ce projet
            $votes = $manager->getRepository(Votes::class)->findBy(['project' => $project]);

            // Supprimer ces entrées
            foreach ($votes as $vote) {
                $manager->remove($vote); // Supprime chaque vote associé au projet
            }


            // 🔹 Supprimer le projet de la base de données
            $manager->remove($project);
            $manager->flush();

            // 🔹 Message de confirmation
            $this->addFlash('success', 'Project successfully deleted.');

            // 🔹 Rediriger vers la liste des projets
            return $this->redirectToRoute('app_projects');
        }






            /* Route pour afficher les project et categorie dans la barre de recherche */

        #[Route('/search', name: 'app_search', methods: ['GET'])]
        public function search(
            Request $request,
            CategoriesRepository $categoriesRepo,
            ProjectsRepository $projectsRepo,
            UsersRepository $usersRepo
        ): JsonResponse {
            $query = $request->query->get('q', ''); // Récupérer le terme de recherche
            if (!$query) {
                return $this->json([]);
            }

            // 🔹 Récupérer les catégories qui commencent par la lettre
            $categories = $categoriesRepo->createQueryBuilder('c')
                ->where('c.name LIKE :query')
                ->setParameter('query', $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            // 🔹 Récupérer les projets qui commencent par la lettre
            $projects = $projectsRepo->createQueryBuilder('p')
                ->where('p.title LIKE :query')
                ->setParameter('query', $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            // 🔹 Récupérer les utilisateurs qui commencent par la lettre
            $users = $usersRepo->createQueryBuilder('u')
                ->where('u.username LIKE :query')
                ->setParameter('query', $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();

            return $this->json([
                'categories' => array_map(fn($c) => ['id' => $c->getId(), 'name' => $c->getName()], $categories),
                    'projects' => array_map(fn($p) => ['id' => $p->getId(), 'title' => $p->getTitle(), 'slug' => $p->getSlug()], $projects),
                'users' => array_map(fn($u) => ['id' => $u->getId(), 'username' => $u->getUsername()], $users),
            ]);
        }
 




            /* route afficher les project d'une categori */

            #[Route('/category/{id}', name: 'app_category_projects')]
        public function categoryProjects(int $id, ProjectsRepository $projectsRepo, CategoriesRepository $categoriesRepo): Response
        {
            // 🔹 Récupérer la catégorie
            $category = $categoriesRepo->find($id);
            if (!$category) {
            throw $this->createNotFoundException('Category not found.');
            }

            // 🔹 Récupérer les projets les plus likés de cette catégorie
            $topProjects = $projectsRepo->findTopProjectsByCategory($id, 10);

            return $this->render('projects/category-project.html.twig', [
            'category' => $category,
            'projects' => $topProjects,
            ]);
        }


}

