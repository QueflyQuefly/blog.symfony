<?php

namespace App\Repository;

use App\Entity\Comments;
use App\Entity\RatingComments;
use App\Repository\RatingCommentsRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comments[]    findAll()
 * @method Comments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function findByUserId(int $userId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.userId = :val')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function findByPostId(int $postId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.postId = :val')
            ->setParameter('val', $postId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
    }
}
