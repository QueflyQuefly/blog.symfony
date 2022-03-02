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
}
