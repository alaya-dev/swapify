<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    //    /**
    //     * @return Blog[] Returns an array of Blog objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Blog
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    // Méthode pour compter les Blogs acceptées
public function countAcceptedBlogs(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'Acceptée') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

// Méthode pour compter les Blogs rejetées
public function countRejectedBlogs(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'Rejetée') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

// Méthode pour compter les Blogs en attente
public function countPendingBlogs(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'enAttente') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

}
