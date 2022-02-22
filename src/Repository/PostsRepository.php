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
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($amountOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }
   
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getMoreTalkedPosts($amountOfPosts)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id <= :val')
            ->setParameter('val', 6)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($amountOfPosts)
            ->getQuery()
            ->getResult()
        ;
    }
       
    /**
     * @return Posts[] Returns an array of Posts objects
     */
    public function getPosts($numberOfPosts, $page)
    {
        $moreThanMinId = $page * $numberOfPosts - $numberOfPosts;

        return $this->createQueryBuilder('p')
            ->andWhere('p.id >= :val')
            ->setParameter('val', $moreThanMinId)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults($numberOfPosts)
            ->getQuery()
            ->getResult()
        ;
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
