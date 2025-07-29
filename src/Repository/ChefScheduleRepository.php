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
