<?php

namespace App\Repository;

use App\Entity\RatingPosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RatingPosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingPosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingPosts[]    findAll()
 * @method RatingPosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingPostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingPosts::class);
    }

    // /**
    //  * @return RatingPosts[] Returns an array of RatingPosts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RatingPosts
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
