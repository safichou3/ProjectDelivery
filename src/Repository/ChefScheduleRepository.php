<?php

namespace App\Repository;

use App\Entity\ChefSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChefSchedule>
 *
 * @method ChefSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChefSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChefSchedule[]    findAll()
 * @method ChefSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChefScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChefSchedule::class);
    }

    public function findAvailableChefs(\DateTimeInterface $dateTime)
    {
        $day = $dateTime->format('l');
        $currentTime = $dateTime->format('H:i:s');
        $currentDate = $dateTime->format('Y-m-d');

        return $this->createQueryBuilder('cs')
            ->innerJoin('cs.chef', 'c')
            ->innerJoin('c.chefProfile', 'cp')
            ->andWhere('cp.approved = true')
            ->andWhere('cs.dayOfWeek = :day')
            ->andWhere('cs.isClosed = false')
            ->andWhere('(cs.exceptionDate IS NULL OR cs.exceptionDate != :currentDate)')
            ->andWhere('cs.openingTime <= :currentTime')
            ->andWhere("
            (CASE 
                WHEN cs.closingTime = '00:00:00' 
                THEN '23:59:59' 
                ELSE cs.closingTime 
             END) >= :currentTime
        ")
            ->setParameter('day', $day)
            ->setParameter('currentTime', $currentTime)
            ->setParameter('currentDate', $currentDate)
            ->getQuery()
            ->getResult();
    }





//    /**
//     * @return ChefSchedule[] Returns an array of ChefSchedule objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ChefSchedule
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
