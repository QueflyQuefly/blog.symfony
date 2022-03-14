<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\InfoPost;
use App\Entity\PostTag;
use App\Entity\RatingPost;
use App\Repository\PostRepository;
use App\Repository\RatingPostRepository;
use App\Repository\InfoPostRepository;
use App\Repository\PostTagRepository;
use Doctrine\ORM\EntityManagerInterface;


class PostService
{
    private PostRepository $postRepository;
    private RatingPostRepository $ratingPostRepository;
    private InfoPostRepository $infoPostRepository;
    private PostTagRepository $postTagRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(      
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        RatingPostRepository $ratingPostRepository,
        InfoPostRepository $infoPostRepository,
        PostTagRepository $postTagRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->ratingPostRepository = $ratingPostRepository;
        $this->infoPostRepository = $infoPostRepository;
        $this->postTagRepository = $postTagRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Post Returns an object of Post
     */
    public function create(User $user, string $title, string $content, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $post = new Post();
        $post->setTitle($title);
        $post->setUser($user);
        $post->setContent($content);
        $post->setDateTime($dateTime);
        $post->setRating('0.0');
        $this->entityManager->persist($post);

        $postInfo = new InfoPost();
        $postInfo->setPost($post);
        $postInfo->setCountComments(0);
        $postInfo->setCountRatings(0);
        $this->entityManager->persist($postInfo);

        $allText = $title." ".$content;
        if (strpos($allText, '#') !== false) {
            $regex = '/#\w+/um';
            preg_match_all($regex, $allText, $tags);
            $tags = $tags[0];
            foreach ($tags as $tag) {
                $this->createTag($tag, $post);
            }
        }
        $this->entityManager->flush();

        return $post;
    }

    /**
     * @return PostTag Returns an object of PostTag
     */
    private function createTag(string $tag, Post $post)
    {
        $tagPost = new PostTag();
        $tagPost->setPost($post);
        $tagPost->setTag($tag);
        $this->entityManager->persist($tagPost);
        return $tagPost;
    }

    /**
     * @return float Returns an float number - rating of post
     */
    private function countRating(Post $post)
    {
        $rating = 0.0;
        $allRatingsPost = $post->getRatingPosts();
        if ($count = $allRatingsPost->count())
        {
            foreach ($allRatingsPost as $ratingPost)
            {
                $rating += $ratingPost->getRating();
            }
            $rating = round($rating / $count, 1);
        }
        return $rating;
    }

    /**
     * @return bool
     */
    public function addRating(User $user, Post $post, int $rating)
    {
        if(!$this->isUserAddRating($user, $post))
        {
            $ratingPost = new RatingPost();
            $ratingPost->setPost($post);
            $ratingPost->setUser($user);
            $ratingPost->setRating($rating);
            $this->entityManager->persist($ratingPost);
    
            $infoPost = $post->getInfoPost();
            $infoPost->setCountRatings($infoPost->getCountRatings() + 1);
            $post->setInfoPost($infoPost);
            $this->entityManager->flush();
    
            $generalRatingPost = $this->countRating($post);
            $post->setRating((string) $generalRatingPost);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isUserAddRating(User $user, Post $post): bool
    {
        if ($this->ratingPostRepository->findOneBy(
            [
                'user' => $user,
                'post' => $post
            ]
        ))
        {
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
     * @return Post[] Returns an array of Post objects
     */
    public function getPosts(int $numberOfPosts, int $page)
    {
        $lessThanMaxId = $page * $numberOfPosts - $numberOfPosts;

        return $this->postRepository->getPosts($numberOfPosts, $lessThanMaxId);
    }

    /**
     * @return Tags[] Returns a Tags objects
     */
    public function getTagsByPostId(int $postId)
    {
        return $this->postTagRepository->findByPostId($postId);
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
        if (strpos($searchWords, '#') === 1)
        {
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

    public function delete($post)
    {
        $postId = $post->getId();
        $this->entityManager->remove($post);
        $infoPost = $this->infoPostRepository->find($postId);
        $this->entityManager->remove($infoPost);
        $this->entityManager->flush();
    }
}