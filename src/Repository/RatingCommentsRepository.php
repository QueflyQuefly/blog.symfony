<?php

namespace App\Repository;

use App\Entity\RatingComments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RatingComments|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingComments|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingComments[]    findAll()
 * @method RatingComments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingCommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingComments::class);
    }

    // /**
    //  * @return RatingComments[] Returns an array of RatingComments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RatingComments
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
