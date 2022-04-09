<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Post $entity, bool $flush = true): void
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
    public function remove(Post $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getLastPosts(int $numberOfResults)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(30)
            ->getResult()
        ;
    }
   
    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getMoreTalkedPosts(int $numberOfResults, int $timeWeekAgo)
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p, u')
            ->join('p.user', 'u')
            ->join('p.comments', 'c')
            ->where('c.dateTime > :time')
            ->andWhere('p.approve = 1')
            ->setParameter('time', $timeWeekAgo)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Post Returns a Post object
     */
    public function getPostById(int $postId)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.id = :id')
            ->andWhere('p.approve = 1')
            ->setParameter(':id', $postId)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getOneOrNullResult()
        ;
    }
       
    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getPosts(int $numberOfResults, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getNotApprovedPosts(int $numberOfResults, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.approve = 0')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getPostsByUserId(int $userId, int $numberOfResults)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.user = :user')
            ->andWhere('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setParameter('user', $userId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getLikedPostsByUserId(int $userId, int $numberOfResults)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->join('p.ratingPosts', 'r')
            ->where('r.user = :user')
            ->andWhere('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setParameter('user', $userId)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->setCacheable(true)
            ->enableResultCache(60)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByTitle(string $search, int $numberOfResults)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select('p, u')
            ->join('p.user', 'u')
            ->where($qb->expr()->like('p.title', ':search'))
            ->andWhere('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByAuthor(string $search, int $numberOfResults)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select('p, u')
            ->join('p.user', 'u')
            ->where($qb->expr()->like('u.fio', ':search'))
            ->andWhere('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByContent(string $search, int $numberOfResults)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select('p, u')
            ->join('p.user', 'u')
            ->where($qb->expr()->like('p.content', ':search'))
            ->andWhere('p.approve = 1')
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults($numberOfResults)
            ->getQuery()
            ->getResult()
        ;
    }
}
