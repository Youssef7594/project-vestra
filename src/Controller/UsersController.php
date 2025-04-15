<?php

namespace App\Controller;

use App\Form\UserProfileType;
use App\Repository\ProjectsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Users;
use App\Repository\FollowersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface; 
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Cloudinary\Cloudinary;


class UsersController extends AbstractController
{
    

#[Route('/network', name: 'app_network')]
public function network(UsersRepository $usersRepo): Response
{
    $recruteurs = $usersRepo->findRandomRecruteurs();
    $talents = $usersRepo->findRandomTalents();

    return $this->render('users/network.html.twig', [
        'recruteurs' => $recruteurs,
        'talents' => $talents,
    ]);
}



/* Route qui affiche le profile de l'utilisateur connecter */
#[Route('/profile', name: 'app_profile')]
public function pageUsers(Request $request, ProjectsRepository $projectsRepo, SluggerInterface $slugger,  EntityManagerInterface $entityManager): Response
{

     // Configuration Cloudinary
     $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
            'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
            'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
        ]
    ]);

  // Récupérer l'utilisateur actuellement connecté
  $user = $this->getUser();

  $roles = $user->getRoles(); // Retourne un tableau des rôles

  // Récupérer les projets de l'utilisateur en utilisant user_id
  $projects = $projectsRepo->findBy(['user_id' => $user->getId()]);

  // Créer et gérer le formulaire de profil utilisateur
  $form = $this->createForm(UserProfileType::class, $user);
  $form->handleRequest($request);

  if ($form->isSubmitted() && $form->isValid()) {
     //  Gestion de la photo de profil
        $profilePictureFile = $form->get('profile_picture')->getData();
        if ($profilePictureFile) {
            try {
                $uploadResult = $cloudinary->uploadApi()->upload($profilePictureFile->getPathname(), [
                    'folder' => 'users/profile_pictures',
                    'public_id' => uniqid()
                ]);
                $user->setProfilePicture($uploadResult['secure_url']); // Stocke l'URL Cloudinary
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du téléversement de l\'image de profil.');
            }
        }

        // 🔹 Gestion de l’image de fond
        $backgroundImageFile = $form->get('background_image')->getData();
        if ($backgroundImageFile) {
            try {
                $uploadResult = $cloudinary->uploadApi()->upload($backgroundImageFile->getPathname(), [
                    'folder' => 'users/background_images',
                    'public_id' => uniqid()
                ]);
                $user->setBackgroundImage($uploadResult['secure_url']);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors du téléversement de l\'image de fond.');
            }
        }

      // Sauvegarder les modifications de l'utilisateur
      $entityManager->flush(); // Remplace $this->getDoctrine()->getManager()->flush();

      $this->addFlash('success', 'Profil mis à jour avec succès!');
      return $this->redirectToRoute('app_profile');
  }

  // Rendre la vue du profil avec les données utilisateur et projets
  return $this->render('users/index.html.twig', [
      'user' => $user,
      'projects' => $projects,
      'roles' => $roles, // Ajouter les rôles à la vue
      'form' => $form->createView(),
  
]);


}




/* Route quand on cherche un utilisateur */
#[Route('/profile/{id}', name: 'app_profile')]
#[IsGranted('ROLE_USER')]
public function pageUsersLink(int $id, UsersRepository $usersRepo, ProjectsRepository $projectsRepo, Security $security, Request $request, EntityManagerInterface $entityManager, FollowersRepository $followersRepo): Response
{

    $user = $usersRepo->find($id);
    $currentUser = $this->getUser();
    $user = $usersRepo->find($id);
    $isOwner = $user === $this->getUser();  // Vérifie si c'est l'utilisateur connecté qui voit son propre profil

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur introuvable');
    }


    // Récupérer les projets de l'utilisateur
    $projects = $projectsRepo->findBy(['user_id' => $user->getId()]);

    // Vérifier si l'utilisateur actuel suit cet utilisateur
    $isFollowing = $followersRepo->findOneBy([
        'follower' => $currentUser,
        'followed' => $user
    ]) !== null;

    // Création du formulaire en passant l'option 'is_owner'
    $form = $this->createForm(UserProfileType::class, $user, [
        'is_owner' => $isOwner,  // Passe l'option is_owner au formulaire
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Traitement du formulaire ici
        $entityManager->flush();
        $this->addFlash('success', 'Profil mis à jour avec succès!');
        return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
    }

    return $this->render('users/index.html.twig', [
        'user' => $user,
        'projects' => $projects,
        'form' => $form->createView(),
        'isOwner' => $isOwner,
        'isFollowing' => $isFollowing,
    ]);
}





/* Route pour le rôle users ou talent */

#[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(AuthorizationCheckerInterface $authChecker): Response
    {
        // Vérifier le rôle de l'utilisateur connecté
        if ($authChecker->isGranted('ROLE_RECRUTEUR')) {
            // Si l'utilisateur est recruteur, afficher le tableau de bord recruteur
            return $this->render('recruteur/dashboard.html.twig');
        }

        if ($authChecker->isGranted('ROLE_TALENT')) {
            // Si l'utilisateur est talent, afficher le tableau de bord talent
            return $this->render('talent/dashboard.html.twig');
        }

        // Si l'utilisateur n'a pas de rôle ou un rôle invalide, rediriger ou afficher une erreur
        return $this->render('error/access_denied.html.twig');
    }





    /* route de la barre de recherche */
    #[Route('/search-users', name: 'search_users', methods: ['GET'])]
    public function searchUsers(Request $request, UsersRepository $usersRepo): Response
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json([]);
        }

        $users = $usersRepo->findUsersByName($query, 10);

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'profilePicture' => $user->getProfilePicture(),
            ];
        }, $users);

        return $this->json($data);
    }
    
}
