<?php

namespace App\Repository;

use App\Entity\Subscriptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Subscriptions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscriptions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscriptions[]    findAll()
 * @method Subscriptions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscriptions::class);
    }
}
