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
    public function getLastPosts(int $amountOfPosts)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                ORDER BY p.id DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setMaxResults($amountOfPosts);

        return $query->getResult();
    }
   
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getMoreTalkedPosts(int $amountOfPosts)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                WHERE a.countComments > 0
                ORDER BY a.countComments DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setMaxResults($amountOfPosts);

        return $query->getResult();
    }
       
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPosts(int $numberOfPosts, int $page)
    {
        $moreThanMinId = $page * $numberOfPosts - $numberOfPosts;
        
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                WHERE p.id >= :val
                ORDER BY p.id ASC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $moreThanMinId)
            ->setMaxResults($numberOfPosts);

        return $query->getResult();
    }
    
    public function getPostById(int $postId)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                WHERE p.id = :val
                ORDER BY p.id ASC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $postId);

        return $query->getOneOrNullResult();
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPostsByUserId(int $userId)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                WHERE u.id = :val
                ORDER BY p.id DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $userId);

        return $query->getResult();
    }

    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getLikedPostsByUserId(int $userId)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.userId, p.dateTime, p.content, 
                    a.rating, a.countComments, a.countRatings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.postId
                JOIN App\Entity\User u
                WITH p.userId = u.id
                JOIN App\Entity\RatingPosts r
                WHERE r.userId = :val
                ORDER BY p.id DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $userId);

        return $query->getResult();
    }

    // /**
    //  * @return Posts[] Returns an array of Posts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Posts
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
