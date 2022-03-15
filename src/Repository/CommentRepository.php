<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Comment $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Comment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
    
    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getComments(int $numberOfComments, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfComments)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getLikedCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this->createQueryBuilder('c')
            ->join('App\Entity\RatingComment', 'r', 'WITH', 'r.comment = c.id')
            ->andWhere('r.user = :val')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($numberOfComments)
            ->getQuery()
            ->getResult()
        ;
    }
}
