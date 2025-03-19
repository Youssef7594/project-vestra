<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Followers;
use App\Entity\Notifications;
use App\Repository\FollowersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class FollowersController extends AbstractController
{
    #[Route('/follow/{userId}', name: 'follow', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function follow(int $userId, UsersRepository $userRepo, FollowersRepository $followersRepo, EntityManagerInterface $em): JsonResponse {
        $user = $this->getUser();
        $followedUser = $userRepo->find($userId);

        if (!$followedUser) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        // Vérifier si l'utilisateur suit déjà cette personne
        $existingFollow = $followersRepo->findOneBy([
            'follower' => $user, 
            'followed' => $followedUser
        ]);

        if ($existingFollow) {
            // Désabonnement
            $em->remove($existingFollow);
            $em->flush();
            return new JsonResponse(['message' => 'Unsubscribe', 'following' => false]);
        }

        // Suivi
        $follow = new Followers();
        $follow->setFollower($user);
        $follow->setFollowed($followedUser);
        $follow->setCreatedAt(new \DateTime());

        $em->persist($follow);
        $em->flush();

         // Créer une notification pour l'utilisateur suivi
         $notification = new Notifications();
         $notification->setUser($followedUser); // L'utilisateur suivi reçoit la notification
         $notification->setType('follow'); // Type d'abonnement
         $notification->setMessage(" {$user->getUsername()} just subscribed to your profile.");
         $notification->setCreatedAt(new \DateTime());
         $notification->setSeen(false); // Non vue au début
 
         $em->persist($notification);
         $em->flush();

        return new JsonResponse(['message' => 'Abonné', 'following' => true]);
    }



    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(FollowersRepository $followersRepo, UsersRepository $usersRepo): Response
    {
        $user = $this->getUser();

        $followers = $followersRepo->findBy(['followed' => $user]);
        $following = $followersRepo->findBy(['follower' => $user]);
        

        return $this->render('followers/index.html.twig', [
            'followers' => array_map(fn($f) => $f->getFollower(), $followers),
    'following' => array_map(fn($f) => $f->getFollowed(), $following),

        ]);
    }
}
