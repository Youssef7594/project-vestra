<?php

namespace App\Controller;

use App\Entity\Votes;
use App\Entity\Notifications; 
use App\Repository\VotesRepository;
use App\Repository\ProjectsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/votes', name: 'app_votes_')]
class VotesController extends AbstractController
{
    #[Route('/like/{projectId}', name: 'app_votes_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(int $projectId, ProjectsRepository $projectRepo, VotesRepository $voteRepo, EntityManagerInterface $em, UsersRepository $userRepo, ): Response
    {

        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();
        
        // Trouver le projet à l'aide de son ID
        $project = $projectRepo->find($projectId);

        if (!$project) {
            return $this->json(['message' => 'Projet not found'], 404);
        }

        // Vérifier si l'utilisateur a déjà voté pour ce projet
        $existingVote = $voteRepo->findOneBy(['user' => $user, 'project' => $project]);

        if ($existingVote) {
            // Si un vote existe déjà, on l'enlève (unlike)
            $em->remove($existingVote);
            $em->flush();
        } else {
            // Si le vote n'existe pas, on ajoute un like
            $vote = new Votes();
            $vote->setUser($user);
            $vote->setProject($project);  // Utilisation de setProject() pour affecter l'objet Project
            $vote->setCreatedAt(new \DateTime());

            $em->persist($vote);
            $em->flush();

            //  Récupérer le propriétaire du projet via son ID
                $owner = $userRepo->find($project->getUserId());

             //  Créer une notification pour le propriétaire du projet
             $notification = new Notifications();
             $notification->setUser($owner); // Ne pas oublier d'assigner l'utilisateur
             $notification->setType('like');
             $notification->setMessage("{$user->getUsername()} liked your project!");
             $notification->setSeen(false); // ✅ Assigner une valeur à 'seen'
             $notification->setCreatedAt(new \DateTime()); // ✅ Ajouter une date de création
            
            $em->persist($notification);
            $em->flush();
        }

        

        // Calculer le nouveau nombre de likes
        $newLikeCount = count($project->getVotes()); // Ou si tu utilises un autre moyen pour calculer les likes

        // Retourner une réponse JSON avec le nouveau nombre de likes
        return $this->json(['likes' => $newLikeCount]);
}




}

