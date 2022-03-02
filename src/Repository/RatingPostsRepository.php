<?php

namespace App\Repository;

use App\Entity\RatingPosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RatingPosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method RatingPosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method RatingPosts[]    findAll()
 * @method RatingPosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RatingPostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RatingPosts::class);
    }

    /**
     * @return float Returns an float number - rating of post
     */
    public function countRating(int $postId)
    {
        $rating = 0.0;
        $i = 0;
        $allRatingsPost = $this->findBy(['postId' => $postId]);
        foreach ($allRatingsPost as $ratingPost)
        {
            $i++;
            $rating += $ratingPost->getRating();
        }
        $rating = round($rating / $i, 1);
        return $rating;
    }
}
