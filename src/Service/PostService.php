<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\RatingPost;
use App\Repository\PostRepository;
use App\Repository\RatingPostRepository;
use App\Service\MailerService;
use App\Service\UserService;

class PostService
{
    private PostRepository $postRepository;
    private RatingPostRepository $ratingPostRepository;
    private MailerService $mailer;
    private UserService $userService;

    public function __construct(
        PostRepository $postRepository,
        RatingPostRepository $ratingPostRepository,
        MailerService $mailer,
        UserService $userService
    ) {
        $this->postRepository = $postRepository;
        $this->ratingPostRepository = $ratingPostRepository;
        $this->mailer = $mailer;
        $this->userService = $userService;
    }

    /**
     * @return Post Returns an object of Post
     */
    public function create(User $user, string $title, string $content, $dateTime = false, bool $flush = true)
    {
        if (!$dateTime) {
            $dateTime = time();
        }
        $post = new Post();
        $post->setTitle($title);
        $post->setUser($user);
        $regex = '/#(\w+)/um';
        $content = preg_replace($regex, "<a class='link' href='/search/%23$1'>$0</a>", $content);
        $post->setContent($content);
        $post->setDateTime($dateTime);
        $post->setRating('0.0');
        $this->postRepository->add($post, $flush);

        if ($flush) {
            $toAddresses = [0 => ['email' => 'drotovmihailo@gmail.com']]; // $this->userService->getSubscribedUsersEmails($user);
            //if (!empty($toAddresses)) {
                if(!$this->mailer->sendMailsToSubscribers($toAddresses, $user, $post->getId())) {
                    return false;
                //}
            }
        }
        
        return $post;
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
    public function addRating(User $user, Post $post, int $rating, bool $checkingForUser = true, bool $flush = true)
    {
        if ($checkingForUser) {
            if(!$this->isUserAddRating($user, $post)) {
                $ratingPost = new RatingPost();
                $ratingPost->setPost($post);
                $ratingPost->setUser($user);
                $ratingPost->setRating($rating);
                $post->setRating((string) $this->countRating($post, $rating));
                $this->ratingPostRepository->add($ratingPost, $flush);

                return true;
            }
        } else {
            $ratingPost = new RatingPost();
            $ratingPost->setPost($post);
            $ratingPost->setUser($user);
            $ratingPost->setRating($rating);
            $post->setRating((string) $this->countRating($post, $rating));
            $this->ratingPostRepository->add($ratingPost, $flush);

            return true;
        }
        
        return false;
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

    public function delete($post, bool $flush = true)
    {
        $this->postRepository->remove($post, $flush);
    }
}