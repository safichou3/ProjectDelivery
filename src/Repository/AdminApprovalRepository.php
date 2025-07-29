<?php

namespace App\Repository;

use App\Entity\AdminApproval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminApproval>
 *
 * @method AdminApproval|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdminApproval|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdminApproval[]    findAll()
 * @method AdminApproval[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminApprovalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminApproval::class);
    }

//    /**
//     * @return AdminApproval[] Returns an array of AdminApproval objects
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

//    public function findOneBySomeField($value): ?AdminApproval
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
