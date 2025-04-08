<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comments;
use App\Entity\Notifications;
use App\Repository\CommentsRepository;
use App\Repository\ProjectsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\DynamoDBService;


class CommentsController extends AbstractController
{

    private $dynamoDBService;

    public function __construct(DynamoDBService $dynamoDBService)
    {
        $this->dynamoDBService = $dynamoDBService;
    }

    #[Route('/comments/{projectId}', name: 'app_comments', methods: ['POST'])]
    public function index(int $projectId, Request $request, EntityManagerInterface $em, Security $security, UsersRepository $usersRepo, ProjectsRepository $projectsRepo): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return new Response('Unauthenticated user', Response::HTTP_FORBIDDEN);
        }

        // Récupérer le projet à partir de son ID
        $project = $projectsRepo->find($projectId);

        if (!$project) {
            return new JsonResponse(['message' => 'Project not found'], 404);
        }

       // Récupérer l'utilisateur propriétaire du projet en utilisant user_id
        $projectOwner = $usersRepo->find($project->getUserId());


        if (!$projectOwner) {
            return new JsonResponse(['message' => 'Project owner user not found'], 404);
        }

    
        $content = $request->request->get('content');
    
        if (!$content) {
            return new Response('Comment is empty', Response::HTTP_BAD_REQUEST);
        }

        
    
        $comment = new Comments();
        $comment->setContent($content);
        $comment->setProjectId($projectId);
        $comment->setUser($user); 
        $comment->setCreatedAt(new \DateTime());
    
        $em->persist($comment);
        $em->flush();

         // Appel à DynamoDB pour ajouter un commentaire
    // Conversion des variables nécessaires en string
    /* $this->dynamoDBService->addComment((string)$projectId, (string)$user->getId(), $content); */


        // Si le propriétaire du projet est différent de l'utilisateur qui a commenté
        if ($projectOwner && $projectOwner !== $user) {
            $notification = new Notifications();
            $notification->setUser($projectOwner); // Propriétaire du projet reçoit la notification
            $notification->setType('comment');
            $notification->setMessage(" {$user->getUsername()} commented on your project '{$project->getTitle()}'.");
            $notification->setCreatedAt(new \DateTime());
            $notification->setSeen(false); // Non vue au départ

            $em->persist($notification);
            $em->flush();
        }
    
        // Retourne une redirection vers la page du projet après le commentaire
        return $this->redirectToRoute('app_projects_project', ['projects' => $projectId]);

    }



    /* Route pour supprimer un commentaire */
    #[Route('/comment/delete/{commentId}', name: 'app_delete_comment', methods: ['POST'])]
    public function deleteComment(int $commentId, EntityManagerInterface $em, CommentsRepository $commentsRepo, Security $security): Response
{
    // Récupérer l'utilisateur connecté
    $user = $security->getUser();
    if (!$user) {
        return new Response('Unauthenticated user', Response::HTTP_FORBIDDEN);
    }

    // Récupérer le commentaire à partir de son ID
    $comment = $commentsRepo->find($commentId);
    
    // Vérifier si le commentaire existe
    if (!$comment) {
        return new Response('Comment not found', Response::HTTP_NOT_FOUND);
    }
    
    if ($comment->getUser() !== $user) {
        return new Response('You cannot delete this comment', Response::HTTP_FORBIDDEN);
    }

    $em->remove($comment);
    $em->flush();

      // Extraire le project_id du commentaire (qui est l'ID du projet)
    /* $projectId = $comment['project_id']['S'];  */

    //  Récupérer le projet en fonction de son ID
    /* $project = $projectRepo->find($projectId);
 */
    // Supprimer le commentaire dans DynamoDB
    /* $dynamoDBService->deleteComment($commentId); */
    

    // Rediriger vers la page du projet après la suppression du commentaire
    return $this->redirectToRoute('app_projects_project', ['projects' => $comment->getProjectId()]);  
}


    
}
