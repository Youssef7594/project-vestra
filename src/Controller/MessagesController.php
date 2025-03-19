<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Users;
use App\Repository\MessagesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



class MessagesController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    #[IsGranted('ROLE_USER')]
    public function index(MessagesRepository $messagesRepo, UsersRepository $usersRepo, Security $security): Response
{
    $user = $security->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('You must be logged in.');
    }

    $conversations = $messagesRepo->findUserConversations($user);

    
    // Vérifie les messages non lus dans chaque conversation
    foreach ($conversations as $conversation) {
        $unreadMessagesCount = $messagesRepo->countUnreadMessagesBetweenUsers($user, $conversation);
        $conversation->hasUnreadMessages = $unreadMessagesCount > 0; // Ajoute un booléen
    }
    
    

    return $this->render('messages/index.html.twig', [
        'conversations' => $conversations, // 🔹 Liste des utilisateurs avec qui il a parlé
        'user' => $user,
    ]);
}



/* Route pour afficher une conversation */

#[Route('/messages/conversation/{id}', name: 'app_messages_conversation', requirements: ['id' => '\d+'])]
public function conversation(int $id, UsersRepository $usersRepo, MessagesRepository $messagesRepo, EntityManagerInterface $entityManager, Security $security): Response
{
    $currentUser = $security->getUser();

    // Récupérer l'utilisateur destinataire
    $user = $usersRepo->find($id);

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    // Récupérer tous les messages entre les deux utilisateurs
    $messages = $messagesRepo->findConversationMessages($currentUser, $user);

    // Marquer les messages reçus comme lus
    foreach ($messages as $message) {
        if ($message->getReceiver() === $currentUser && !$message->isRead()) {
            $message->setIsRead(true);
            $entityManager->persist($message);
        }
    }
    $entityManager->flush();

    $unreadMessagesCount = $messagesRepo->countUnreadMessagesBetweenUsers($currentUser, $user);

    // (optionnel) Tu peux passer ce nombre à la vue si tu veux l'afficher en temps réel

    return $this->render('messages/conversation.html.twig', [
        'messages' => $messages,
        'recipient' => $user,
    ]);
}






/* Route pour envoyer un messages */

#[Route('/messages/send/{id}', name: 'app_messages_send', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function sendMessage(int $id, Users $user, Request $request, EntityManagerInterface $entityManager, Security $security): Response
{
    $currentUser = $security->getUser();
        $content = $request->request->get('message');

        if (!$content) {
            return $this->redirectToRoute('app_messages_conversation', ['id' => $id]);
        }

        $user = $entityManager->getRepository(Users::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $message = new Messages();
        $message->setSender($currentUser);
        $message->setReceiver($user);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTime());

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_messages_conversation', ['id' => $id]);
}




/* Route pour avoir le petit rond rouge de message non lue */

#[Route('/header-messages', name: 'header_messages')]
public function headerMessages(MessagesRepository $messagesRepo, Security $security): Response
{
    $user = $security->getUser(); // Récupérer l'utilisateur connecté
    $hasUnreadMessages = false; // Par défaut, pas de message non lu

    if ($user) {
        $hasUnreadMessages = $messagesRepo->hasUnreadMessages($user); // Utiliser la fonction pour vérifier si l'utilisateur a des messages non lus
    }

    return $this->render('partials/messages_badge.html.twig', [
        'hasUnreadMessages' => $hasUnreadMessages, // Passer l'information de l'état des messages non lus
    ]);
}



    /* Route pour supprimer un message dans une conversation  */

    #[Route('/messages/delete/{messageId}', name: 'app_messages_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteMessage(int $messageId, MessagesRepository $messagesRepo, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();
        $message = $messagesRepo->find($messageId);

        if (!$message) {
            return $this->redirectToRoute('app_messages');
        }

        // Vérifie si l'utilisateur est bien le propriétaire du message
        if ($message->getSender() !== $user) {
            $this->addFlash('error', 'You can only delete your own messages.');
            return $this->redirectToRoute('app_messages_conversation', ['id' => $message->getReceiver()->getId()]);
        }

        // Suppression du message
        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message supprimé avec succès.');

        return $this->redirectToRoute('app_messages_conversation', ['id' => $message->getReceiver()->getId()]);
    }

}





