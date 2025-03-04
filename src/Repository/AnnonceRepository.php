<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Annonce>
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

//    /**
//     * @return Annonce[] Returns an array of Annonce objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Annonce
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findFilteredAnnonces($titre, $cat, $reg, $date)
{
    $queryBuilder = $this->createQueryBuilder('a')
        ->andWhere('a.statut = :statut')
        ->setParameter('statut', 'Acceptee'); 

    if ($titre) {
        $queryBuilder->andWhere('a.titre LIKE :titre OR a.description LIKE :titre')
                     ->setParameter('titre', '%' . $titre . '%');
    }

    if ($cat) {
        $queryBuilder->andWhere('a.categorie = :categorie')
                     ->setParameter('categorie', $cat);
    }

    if ($reg) {
        $queryBuilder->andWhere('a.region = :reg')
                     ->setParameter('reg', $reg);
    }

    if ($date) {
        $now = new \DateTime();

        switch ($date) {
            case 'today':
                $queryBuilder->andWhere('a.dateCreation >= :date')
                             ->setParameter('date', $now->format('Y-m-d 00:00:00'));
                break;
            case 'last_week':
                $lastWeek = (new \DateTime())->modify('-7 days');
                $queryBuilder->andWhere('a.dateCreation >= :date')
                             ->setParameter('date', $lastWeek->format('Y-m-d 00:00:00'));
                break;
            case 'last_month':
                $lastMonth = (new \DateTime())->modify('-1 month');
                $queryBuilder->andWhere('a.dateCreation >= :date')
                             ->setParameter('date', $lastMonth->format('Y-m-d 00:00:00'));
                break;
            case 'older':
                $oneMonthAgo = (new \DateTime())->modify('-1 month');
                $queryBuilder->andWhere('a.dateCreation < :date')
                             ->setParameter('date', $oneMonthAgo->format('Y-m-d 00:00:00'));
                break;
        }
    }

    return $queryBuilder->getQuery()->getResult();
}




public function findEnAttAnnonces(): array
{
    return $this->createQueryBuilder('a')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'en attente')
        ->orderBy('a.dateCreation', 'DESC') 
        ->getQuery()
        ->getResult();
}

public function findValiderAnnonces(): array
{
    return $this->createQueryBuilder('a')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'Acceptee')
        ->orderBy('a.dateCreation', 'DESC') 
        ->getQuery()
        ->getResult();
}

public function findValideAnnoncesByUsrId($idU): array
{
    return $this->createQueryBuilder('a')
        ->where('a.statut = :statut')
        ->andWhere('a.user = :userId') 
        ->setParameter('statut', 'Acceptee')
        ->setParameter('userId', $idU)
        ->orderBy('a.dateCreation', 'DESC') 
        ->getQuery()
        ->getResult();
}

public function findAnnoncesByUsrId($idU): array
{
    return $this->createQueryBuilder('a')
        ->andWhere('a.user = :userId') 
        ->setParameter('userId', $idU)
        ->orderBy('a.dateCreation', 'DESC') 
        ->getQuery()
        ->getResult();
}

// Méthode pour compter les annonces acceptées
public function countAcceptedAnnonces(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'Acceptée') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

// Méthode pour compter les annonces rejetées
public function countRejectedAnnonces(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'Rejetée') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

// Méthode pour compter les annonces en attente
public function countPendingAnnonces(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('count(a.id)')
        ->where('a.statut = :statut')
        ->setParameter('statut', 'En Attente') // Modifier selon ton statut
        ->getQuery()
        ->getSingleScalarResult();
}

}
