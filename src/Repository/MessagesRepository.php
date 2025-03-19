<?php

namespace App\Repository;

use App\Entity\Messages;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Messages>
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }


/* Récupére la coneversation sans doublon */

public function findUserConversations(Users $user): array
{
    return $this->createQueryBuilder('m')
        ->select('DISTINCT u') // Sélectionne l'entité utilisateur distincte
        ->leftJoin('m.sender', 's')
        ->leftJoin('m.receiver', 'r')
        ->join(Users::class, 'u', 'WITH', 'u = s OR u = r') // Associe l'entité Users
        ->where('m.sender = :user OR m.receiver = :user')
        ->andWhere('u != :user') // Exclut l'utilisateur lui-même
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}


/**
     * Récupérer tous les messages échangés entre deux utilisateurs
     */
    public function findConversationMessages(Users $sender, Users $receiver)
    {
        $qb = $this->createQueryBuilder('m')
        ->where('m.sender = :sender AND m.receiver = :receiver')
        ->orWhere('m.sender = :receiver AND m.receiver = :sender')
        ->setParameter('sender', $sender)
        ->setParameter('receiver', $receiver)
        ->orderBy('m.created_at', 'ASC');  // Utilise "created_at" au lieu de "createdAt"

    return $qb->getQuery()->getResult();
    }


    /* Message non lue affichege compteur */

 /*    public function countUnreadMessages(Users $user): int
{
    return $this->createQueryBuilder('m')
    ->select('COUNT(m.id)')
    ->where('m.receiver = :user')
    ->andWhere('m.is_read = false') // Vérifie que les messages sont non lus
    ->andWhere('m.sender != :user') // Exclut les messages envoyés par l'utilisateur
    ->setParameter('user', $user)
    ->getQuery()
    ->getSingleScalarResult();
} */


/* Récupére le nombre de message non lus     */
public function hasUnreadMessages(Users $user): bool
{
    return (bool) $this->createQueryBuilder('m')
        ->select('COUNT(m.id)')
        ->where('m.receiver = :user')
        ->andWhere('m.is_read = false')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleScalarResult();
}



/* Compte les message non lue pour un utilisateur */
public function countUnreadMessagesBetweenUsers(Users $currentUser, Users $recipient): int
{
    return $this->createQueryBuilder('m')
        ->select('COUNT(m.id)')
        ->where('m.receiver = :currentUser')  // Seuls les messages reçus comptent
        ->andWhere('m.sender = :recipient')  // Les messages envoyés par l'autre utilisateur
        ->andWhere('m.is_read = false')  // Seulement les non lus
        ->setParameter('currentUser', $currentUser)
        ->setParameter('recipient', $recipient)
        ->getQuery()
        ->getSingleScalarResult();
}



    //    /**
    //     * @return Messages[] Returns an array of Messages objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Messages
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
