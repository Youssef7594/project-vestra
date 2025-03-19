<?php

namespace App\Controller;

use App\Repository\NotificationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class NotificationsController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    #[IsGranted('ROLE_USER')]
    public function index(NotificationsRepository $notificationRepo): Response
{
    $user = $this->getUser();
    $notifications = $notificationRepo->findBy(['user' => $user], ['created_at' => 'DESC']);

    return $this->render('notifications/index.html.twig', [
        'notifications' => $notifications,
    ]);
}


// ✅ Route pour supprimer toutes les notifications
#[Route('/notifications/clear', name: 'app_notifications_clear', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function clearNotifications(EntityManagerInterface $em, NotificationsRepository $notificationsRepo): Response
{
    $user = $this->getUser();
    $notifications = $notificationsRepo->findBy(['user' => $user]);

    foreach ($notifications as $notification) {
        $em->remove($notification);
    }

    $em->flush();

    return $this->redirectToRoute('app_notifications');
}



#[Route('/header-notifications', name: 'header_notifications')]
    public function headerNotifications(NotificationsRepository $notificationRepo): Response
    {
        $user = $this->getUser();
        $unreadCount = 0;

        if ($user) {
            $unreadCount = $notificationRepo->count(['user' => $user, 'seen' => false]);
        }

        return $this->render('partials/notifications_badge.html.twig', [
            'unreadCount' => $unreadCount,
        ]);
    }

}
