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
        $config = new \Doctrine\ORM\Configuration();
        $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_queries', 3600);
        $config->setQueryCache($cache);
        $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_results', 3600);
        $config->setResultCache($cache);
        $cache = new \Symfony\Component\Cache\Adapter\PhpFilesAdapter('doctrine_metadata', 3600);
        $config->setMetadataCache($cache);
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
    public function getLastPosts(int $numberOfPosts)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u, COUNT(c.id) as countComments, COUNT(r.id) as countRatings')
            ->join('p.user', 'u')
            ->join('p.comments', 'c')
            ->join('p.ratingPosts', 'r')
            ->groupBy('p.id')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult()
        ;
    }
   
    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getMoreTalkedPosts(int $numberOfPosts, int $timeWeekAgo)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u, COUNT(c.id) as countComments, COUNT(r.id) as countRatings')
            ->join('p.user', 'u')
            ->join('p.ratingPosts', 'r')
            ->join('App\Entity\Comment', 'c', 'WITH', 'c.post = p')
            ->where('c.dateTime > :time')
            ->setParameter('time', $timeWeekAgo)
            ->groupBy('p.id')
            ->orderBy('c.post', 'DESC')
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult()
        ;
    }
       
    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getPosts(int $numberOfPosts, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('p')
            ->select('p, u, c, r')
            ->join('p.user.fio', 'u')
            ->join('p.comments.count', 'c')
            ->join('p.ratingPosts.count', 'r')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getLikedPostsByUserId(int $userId, int $numberOfPosts)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.user.fio', 'u')
            ->join('p.comments.count', 'c')
            ->join('p.ratingPosts.count', 'r')
            ->join('p.ratingPosts', 'rp')
            ->where('rp.user = :val')
            ->orderBy('p.comments.count', 'DESC')
            ->setParameter('val', $userId)
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByTag(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->join('App\Entity\PostTag', 't', 'WITH', 't.post = p.id')
            ->where($qb->expr()->like('t.tag', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByTitle(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where($qb->expr()->like('p.title', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByAuthor(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->join('App\Entity\User', 'u', 'WITH', 'p.user = u.id')
            ->where($qb->expr()->like('u.fio', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchByContent(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where($qb->expr()->like('p.content', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
}
