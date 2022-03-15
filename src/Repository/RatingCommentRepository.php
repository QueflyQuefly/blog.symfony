<?php

namespace App\Repository;

use App\Entity\RatingComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RatingComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingComment[]    findAll()
 * @method RatingComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingComment::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(RatingComment $entity, bool $flush = true): void
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
    public function remove(RatingComment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
