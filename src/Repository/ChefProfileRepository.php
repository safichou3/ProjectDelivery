<?php

namespace App\Repository;

use App\Entity\ChefProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChefProfile>
 *
 * @method ChefProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChefProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChefProfile[]    findAll()
 * @method ChefProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChefProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChefProfile::class);
    }

//    /**
//     * @return ChefProfile[] Returns an array of ChefProfile objects
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

//    public function findOneBySomeField($value): ?ChefProfile
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
