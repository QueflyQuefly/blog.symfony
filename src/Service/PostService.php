<?php

namespace App\Service;

use App\Entity\Posts;
use App\Entity\AdditionalInfoPosts;
use App\Entity\RatingPosts;
use App\Repository\PostsRepository;
use App\Repository\RatingPostsRepository;
use App\Repository\AdditionalInfoPostsRepository;
use Doctrine\Persistence\ManagerRegistry;


class PostService
{
    private ManagerRegistry $doctrine;
    private PostsRepository $postsRepository;
    private RatingPostsRepository $ratingPostsRepository;
    private AdditionalInfoPostsRepository $additionalInfoPostsRepository;

    public function __construct(      
        ManagerRegistry $doctrine,
        PostsRepository $postsRepository,
        RatingPostsRepository $ratingPostsRepository,
        AdditionalInfoPostsRepository $additionalInfoPostsRepository
        )
    {
        $this->postsRepository = $postsRepository;
        $this->ratingPostsRepository = $ratingPostsRepository;
        $this->doctrine = $doctrine;
        $this->additionalInfoPostsRepository = $additionalInfoPostsRepository;
    }

    public function addRating(int $postId, int $rating, int $sessionUserId)
    {
        $entityManager = $this->doctrine->getManager();

        $ratingPost = new RatingPosts();
        $ratingPost->setPostId($postId);
        $ratingPost->setUserId($sessionUserId);
        $ratingPost->setRating($rating);
        $entityManager->persist($ratingPost);
        $entityManager->flush();

        $infoPost = $this->additionalInfoPostsRepository->find($postId);
        $infoPost->setCountRatings($infoPost->getCountRatings() + 1);

        $generalRatingPost = $this->ratingPostsRepository->countRating($postId);
        $infoPost->setRating((string) $generalRatingPost);
        $entityManager->flush();
    }
}