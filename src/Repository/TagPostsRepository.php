<?php

namespace App\Repository;

use App\Entity\TagPosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TagPosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagPosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagPosts[]    findAll()
 * @method TagPosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagPostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagPosts::class);
    }
}
