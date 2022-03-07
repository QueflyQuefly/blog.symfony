<?php

namespace App\Repository;

use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Posts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Posts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Posts[]    findAll()
 * @method Posts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getLastPosts(int $numberOfPosts)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }
   
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getMoreTalkedPosts(int $numberOfPosts, int $timeWeekAgo)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('DISTINCT p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->join('App\Entity\Comments', 'c', 'WITH', 'c.postId = p.id')
            ->andWhere('a.countComments > 0')
            ->andWhere('c.dateTime > :time')
            ->setParameter('time', $timeWeekAgo)
            ->orderBy('a.countComments', 'DESC')
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }
       
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPosts(int $numberOfPosts, int $lessThanMaxId)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * @return Posts Returns a Posts object
     */
    public function getPostById(int $postId)
    {
        return $this->createQueryBuilder('p')
            ->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 'p.content', 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->andWhere('p.id = :val')
            ->setParameter('val', $postId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPostsByUserId(int $userId, int $numberOfPosts)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->andWhere('u.id = :val')
            ->orderBy('p.id', 'DESC')
            ->setParameter('val', $userId)
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getLikedPostsByUserId(int $userId, int $numberOfPosts)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->join('App\Entity\RatingPosts', 'r', 'WITH', 'r.postId = p.id')
            ->andWhere('r.userId = :val')
            ->orderBy('p.id', 'DESC')
            ->setParameter('val', $userId)
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function searchByTag(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->join('App\Entity\TagPosts', 't', 'WITH', 't.postId = p.id')
            ->andWhere($qb->expr()->like('t.tag', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function searchByTitle(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->andWhere($qb->expr()->like('p.title', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function searchByAuthor(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->andWhere($qb->expr()->like('u.fio', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function searchByContent(string $search)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->select(array('p.id', 'p.title', 'p.userId', 'p.dateTime', 
                "{$qb->expr()->substring('p.content', 1, 430)} as content", 
                'a.rating', 'a.countComments', 'a.countRatings', 'u.fio as author'))
            ->join('App\Entity\User', 'u', 'WITH', 'p.userId = u.id')
            ->join('App\Entity\AdditionalInfoPosts', 'a', 'WITH', 'a.postId = p.id')
            ->andWhere($qb->expr()->like('p.content', ':search'))
            ->orderBy('p.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
}
