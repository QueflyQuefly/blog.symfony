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
    public function getLastPosts($amountOfPosts)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.post_id
                JOIN App\Entity\User u
                WITH p.user_id = u.id
                ORDER BY p.id DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setMaxResults($amountOfPosts);

        return $query->getResult();
    }
   
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getMoreTalkedPosts($amountOfPosts)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.post_id
                JOIN App\Entity\User u
                WITH p.user_id = u.id
                WHERE a.count_comments > 0
                ORDER BY a.count_comments DESC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setMaxResults($amountOfPosts);

        return $query->getResult();
    }
       
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPosts($numberOfPosts, $page)
    {
        $moreThanMinId = $page * $numberOfPosts - $numberOfPosts;
        
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.post_id
                JOIN App\Entity\User u
                WITH p.user_id = u.id
                WHERE p.id >= :val
                ORDER BY p.id ASC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $moreThanMinId)
            ->setMaxResults($numberOfPosts);

        return $query->getResult();
    }
    
    public function getPostById($postId)
    {
        $entityManager = $this->getEntityManager();
        $dql = 'SELECT p.id, p.title, p.user_id, p.date_time, p.content, 
                    a.rating, a.count_comments, a.count_ratings, u.fio as author 
                FROM App\Entity\Posts p 
                JOIN App\Entity\AdditionalInfoPosts a 
                WITH p.id = a.post_id
                JOIN App\Entity\User u
                WITH p.user_id = u.id
                WHERE p.id = :val
                ORDER BY p.id ASC'
        ;
        $query = $entityManager->createQuery($dql)
            ->setParameter('val', $postId);

        return $query->getOneOrNullResult();
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
