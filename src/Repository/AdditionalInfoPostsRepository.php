<?php

namespace App\Repository;

use App\Entity\AdditionalInfoPosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdditionalInfoPosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdditionalInfoPosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdditionalInfoPosts[]    findAll()
 * @method AdditionalInfoPosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdditionalInfoPostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdditionalInfoPosts::class);
    }

    /**
     * @return AdditionalInfoPosts Returns an  AdditionalInfoPosts object
     */
    public function findOneByPostId($postId): ?AdditionalInfoPosts
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.post_id = :val')
            ->setParameter('val', $postId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
   

    // /**
    //  * @return AdditionalInfoPosts[] Returns an array of AdditionalInfoPosts objects
    //  */
    /*
    public function findOneBySomeField($value): ?AdditionalInfoPosts
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
