<?php

namespace App\Repository;

use App\Entity\Projects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * @extends ServiceEntityRepository<Projects>
 */
class ProjectsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projects::class);
    }


    
    /* Trouver un projet par son slug (un seul projet)  */
    public function findProjectBySlug(string $slug): ?Projects
    {
        return $this->createQueryBuilder('p')
        ->andWhere('p.slug = :slug')
        ->setParameter('slug', $slug)
        ->getQuery()
        ->getOneOrNullResult();
    }


    /* Cette function sert a afficher des projects au hasard */
    public function findRandomProjects(int $limit)
    {
         // Obtenir la connexion à la base de données
    $conn = $this->getEntityManager()->getConnection();

    // La requête SQL pour récupérer des projets aléatoires
    $sql = 'SELECT * FROM projects ORDER BY RAND() LIMIT :limit';

    // Préparer la requête
    $stmt = $conn->prepare($sql);
    $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

    // Exécuter la requête et récupérer les résultats
    return $stmt->executeQuery()->fetchAllAssociative();
    }



    /* classement des project par like (page classement) */
    public function findTopProjects(int $limit = 10): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT p.*, COUNT(v.id) as like_count
        FROM projects p
        LEFT JOIN votes v ON v.project_id = p.id
        GROUP BY p.id
        ORDER BY like_count DESC
        LIMIT :limit
    ';

    $stmt = $conn->prepare($sql);
    $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

    $resultSet = $stmt->executeQuery();

    return $resultSet->fetchAllAssociative();
}


        /* les 10 projects les plus like d'une catégorie */


        public function findTopProjectsByCategory(int $categoryId, int $limit = 10): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = '
        SELECT p.*, COUNT(v.id) as like_count
        FROM projects p
        LEFT JOIN votes v ON v.project_id = p.id
        WHERE p.category_id = :category_id
        GROUP BY p.id
        ORDER BY like_count DESC
        LIMIT :limit
    ';

    $stmt = $conn->prepare($sql);
    $stmt->bindValue('category_id', $categoryId, \PDO::PARAM_INT);
    $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
    
    $resultSet = $stmt->executeQuery();

    return $resultSet->fetchAllAssociative();
}
    


    /* Trouver les image d'un projet */
    /* public function  findImageOfTheProject($imageProject)
    {
        $dql = $this->createQueryBuilder('p');

    
    if (!empty($imageProject)) {
        $dql->andWhere('p.images LIKE :imageProject')
            ->setParameter('imageProject', '%' . $imageProject . '%');
    }

    return $dql->getQuery()->getResult();
    } */

    //    /**
    //     * @return Projects[] Returns an array of Projects objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Projects
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
