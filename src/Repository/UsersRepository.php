<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }



    /* Trouver un utilisateurs qui a le rôle de recruteur */

    public function findRandomRecruteurs(int $limit = 10): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT * FROM users WHERE role = :role ORDER BY RAND() LIMIT :limit';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('role', 'recruteur');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }


    /* Trouver un utilisateurs qui a le rôle de talent */

    public function findRandomTalents(int $limit = 10): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT * FROM users WHERE role = :role ORDER BY RAND() LIMIT :limit';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('role', 'talent');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }


    /* trouver un utilisateur dans la barre de recheche */
    public function findUsersByName(string $query, int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->setParameter('query', $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }



    /* Trouver un utilisateur par son slug */

 /*    public function findOneBySlug(string $slug): ?Users
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.slug = :slug')
        ->setParameter('slug', $slug)
        ->getQuery()
        ->getOneOrNullResult();
}
 */


}







    //    /**
    //     * @return Users[] Returns an array of Users objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Users
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
