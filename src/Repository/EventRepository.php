<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

public function findEnAttEvents(): array
{
    return $this->createQueryBuilder('e')
        ->where('e.status = :status')
        ->setParameter('status', 'en attente')
        ->getQuery()
        ->getResult();
}

public function findValidateEvents():array{

    return $this->createQueryBuilder('event')
        ->where('event.status = :status')
        ->setParameter('status', 'Acceptee')
        ->getQuery()
        ->getResult();
}

public function findValideEventsByUserId($user_id){

    return $this->createQueryBuilder('event')
    ->where('a.status=:status')
    ->andWhere('a.user=:user_id')
    ->setParameter('status','Acceptee')
    ->setParameter('user_id',$user_id)
    ->getQuery()
    ->getResult();
}
}
