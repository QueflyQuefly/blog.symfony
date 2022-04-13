<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\RatingPost;
use App\Repository\PostRepository;
use App\Repository\RatingPostRepository;
use App\Service\UserService;

class PostService
{
    private PostRepository $postRepository;
    private RatingPostRepository $ratingPostRepository;
    private UserService $userService;

    public function __construct(
        PostRepository $postRepository,
        RatingPostRepository $ratingPostRepository,
        UserService $userService
    ) {
        $this->postRepository = $postRepository;
        $this->ratingPostRepository = $ratingPostRepository;
        $this->userService = $userService;
    }

    /**
     * @return Post Returns an object of Post
     */
    public function create(
        User $user,
        string $title,
        string $content,
        bool $approve = false,
        ?int $dateTime = null,
        bool $flush = true
    ) {
        if (empty($dateTime)) {
            $dateTime = time();
        }
        $post = (new Post())
            ->setTitle($title)
            ->setUser($user)
            ->setContent($content)
            ->setDateTime($dateTime)
            ->setRating('0.0')
            ->setApprove($approve)
        ;
        $this->postRepository->add($post, $flush);
        
        return $post;
    }

    public function approve(Post $post, bool $flush = true)
    {
        $this->postRepository->approve($post, $flush);

        if ($flush) {
            $this->userService->sendMailsToSubscribers($post);
        }
    }

    /**
     * @return float Returns an float number - rating of post
     */
    private function countRating(Post $post, int $rating = 0)
    {
        $allRatingsPost = $post->getRatingPosts();
        if ($count = $allRatingsPost->count()) {
            foreach ($allRatingsPost as $ratingPost) {
                $rating += $ratingPost->getRating();
            }
            $rating = round($rating / ($count + 1), 1);
        }
        return $rating;
    }

    /**
     * @return bool Returns true if rating to post added
     */
    public function addOrRemoveRating(User $user, Post $post, int $rating = 0, bool $checkingForUser = true, bool $flush = true)
    {
        if ($checkingForUser) {
            if(!$this->isUserAddRating($user, $post)) {
                $ratingPost = (new RatingPost())
                    ->setPost($post)
                    ->setUser($user)
                    ->setRating($rating)
                ;
                $post->setRating((string) $this->countRating($post, $rating));
                $this->ratingPostRepository->add($ratingPost, $flush);

                return true;
            } else {
                $ratingPost = $this->ratingPostRepository->findOneBy([
                    'user' => $user,
                    'post' => $post
                ]);
                $this->ratingPostRepository->remove($ratingPost);
                $post->setRating((string) $this->countRating($post));

                return false;
            }
        } else {
            $ratingPost = (new RatingPost())
                ->setPost($post)
                ->setUser($user)
                ->setRating($rating)
            ;
            $post->setRating((string) $this->countRating($post, $rating));
            $this->ratingPostRepository->add($ratingPost, $flush);

            return true;
        }
    }

    /**
     * @return bool Returns true if user added rating to this post
     */
    public function isUserAddRating(User $user, Post $post): bool
    {
        if ($this->ratingPostRepository->findOneBy([
            'user' => $user,
            'post' => $post
        ])) {
            return true;
        }
        return false;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getLastPosts(int $amountOfPosts)
    {
        return $this->postRepository->getLastPosts($amountOfPosts);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getMoreTalkedPosts(int $amountOfPosts)
    {
        $timeWeekAgo = round(time() / 10000, 0) * 10000 - 7*24*60*60;
        return $this->postRepository->getMoreTalkedPosts($amountOfPosts, $timeWeekAgo);
    }

    /**
     * @return Post Returns a Post object
     */
    public function getPostById(int $postId)
    {
        return $this->postRepository->getPostById($postId);
    }

    /**
     * @return Post Returns a Post object
     */
    public function getNotApprovedPostById(int $postId)
    {
        return $this->postRepository->getNotApprovedPostById($postId);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getPosts(int $numberOfPosts, int $page)
    {
        $lessThanMaxId = $page * $numberOfPosts - $numberOfPosts;
        return $this->postRepository->getPosts($numberOfPosts, $lessThanMaxId);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getNotApprovedPosts(int $numberOfPosts, int $page)
    {
        $lessThanMaxId = $page * $numberOfPosts - $numberOfPosts;
        return $this->postRepository->getNotApprovedPosts($numberOfPosts, $lessThanMaxId);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getPostsByUserId(int $userId, int $numberOfPosts)
    {
        return $this->postRepository->getPostsByUserId($userId, $numberOfPosts);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function getLikedPostsByUserId(int $userId, int $numberOfPosts)
    {
        return $this->postRepository->getLikedPostsByUserId($userId, $numberOfPosts);
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function searchPosts(string $searchWords, int $numberOfResults)
    {
        $numberOfResults = $numberOfResults / 4;
        $searchWords = '%' . $searchWords . '%';

        $posts = $this->postRepository->searchByTitle($searchWords, $numberOfResults);
        $posts1 = $this->postRepository->searchByAuthor($searchWords, $numberOfResults);
        $posts2 = $this->postRepository->searchByContent($searchWords, $numberOfResults);
        $results = array_merge($posts, $posts1, $posts2);
        return $results;
    }

    /**
     * @return bool Returns true if Post updated
     */
    public function update(Post $post, bool $flush = true)
    {
        if ($post->getId() && $flush) {
            $this->postRepository->update($flush);

            return true;
        }

        return false;
    }

    public function delete($post, bool $flush = true)
    {
        $this->postRepository->remove($post, $flush);
    }
}