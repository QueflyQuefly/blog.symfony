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
     * @return AdditionalInfoPosts Returns an AdditionalInfoPosts object
     */
    public function findOneByPostId(int $postId): ?AdditionalInfoPosts
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.postId = :val')
            ->setParameter('val', $postId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
