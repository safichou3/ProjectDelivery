<?php

namespace App\Repository;

use App\Entity\FavoriteDish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FavoriteDish>
 *
 * @method FavoriteDish|null find($id, $lockMode = null, $lockVersion = null)
 * @method FavoriteDish|null findOneBy(array $criteria, array $orderBy = null)
 * @method FavoriteDish[]    findAll()
 * @method FavoriteDish[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteDishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteDish::class);
    }

//    /**
//     * @return FavoriteDish[] Returns an array of FavoriteDish objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FavoriteDish
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
