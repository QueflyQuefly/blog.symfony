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
    public function approve(Comment $entity, bool $flush = true): void
    {
        $entity->setApprove(true);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(bool $flush = true): void
    {
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
    public function getComments(int $numberOfResults, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->where('c.approve = 1')
            ->orderBy('c.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getNotApprovedComments(int $numberOfResults, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->where('c.approve = 0')
            ->orderBy('c.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByPostId($postId, $numberOfResults = 50)
    {
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->where('c.post = :id')
            ->andWhere('c.approve = true')
            ->setParameter('id', $postId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByUserId(int $userId, int $numberOfResults)
    {
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->where('c.user = :val')
            ->andWhere('c.approve = true')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getLikedCommentsByUserId(int $userId, int $numberOfResults)
    {
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->join('c.ratingComments', 'r')
            ->where('r.user = :val')
            ->andWhere('c.approve = true')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }
}
