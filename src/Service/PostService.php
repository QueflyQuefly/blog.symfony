<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\PostTag;
use App\Entity\RatingPost;
use App\Repository\PostRepository;
use App\Repository\RatingPostRepository;
use App\Repository\PostTagRepository;

class PostService
{
    private PostRepository $postRepository;
    private RatingPostRepository $ratingPostRepository;
    private PostTagRepository $postTagRepository;

    public function __construct(
        PostRepository $postRepository,
        RatingPostRepository $ratingPostRepository,
        PostTagRepository $postTagRepository
    ) {
        $this->postRepository = $postRepository;
        $this->ratingPostRepository = $ratingPostRepository;
        $this->postTagRepository = $postTagRepository;
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
        $post->setContent($content);
        $post->setDateTime($dateTime);
        $post->setRating('0.0');

        $allText = $title . ' ' . $content;
        if (strpos($allText, '#') !== false) {
            $regex = '/#\w+/um';
            preg_match_all($regex, $allText, $tags);
            $tags = $tags[0];
            foreach ($tags as $tag) {
                $this->createTag($tag, $post);
            }
        }
        $this->postRepository->add($post, $flush);
        
        return $post;
    }

    /**
     * @return PostTag Returns an object of PostTag
     */
    private function createTag(string $tag, Post $post, bool $flush = false)
    {
        $tagPost = new PostTag();
        $tagPost->setPost($post);
        $tagPost->setTag($tag);
        $this->postTagRepository->add($tagPost, $flush);

        return $tagPost;
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
     * @return bool
     */
    public function addRating(User $user, Post $post, int $rating, bool $flush = true)
    {
        if(!$this->isUserAddRating($user, $post)) {
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
     * @return bool
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
        $timeWeekAgo = time() - 7*24*60*60;
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
    public function searchPosts(string $searchWords)
    {
        $searchWords = '%'.$searchWords.'%';
        if (strpos($searchWords, '#') === 1) {
            $searchWords = str_replace('#', '', $searchWords);
            $results = $this->postRepository->searchByTag($searchWords);
        } else {
            $posts = $this->postRepository->searchByTitle($searchWords);
            $posts1 = $this->postRepository->searchByAuthor($searchWords);
            $posts2 = $this->postRepository->searchByContent($searchWords);
            $results = array_merge($posts, $posts1, $posts2);
        }
        return $results;
    }

    public function delete($post, bool $flush = true)
    {
        $this->postRepository->remove($post, $flush);
    }
}