<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Subscription $entity, bool $flush = true): void
    {
        $this
            ->_em
            ->persist($entity);

        if ($flush) {
            $this
                ->_em
                ->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Subscription $entity, bool $flush = true): void
    {
        $this
            ->_em
            ->remove($entity);

        if ($flush) {
            $this
                ->_em
                ->flush();
        }
    }
}
