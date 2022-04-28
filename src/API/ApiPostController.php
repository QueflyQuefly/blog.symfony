<?php

namespace App\API;

use App\Entity\Post;
use App\Entity\Comment;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RedisCacheService;
use App\Normalizer\PostNormalizer;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiPostController extends AbstractController
{
    public const MAX_SIZE_OF_IMAGE = 4194304; // 4 megabytes (4*1024*1024 bytes)

    private PostService $postService;

    private CommentService $commentService;

    private RedisCacheService $cacheService;

    public function __construct(
        PostService       $postService,
        CommentService    $commentService,
        RedisCacheService $cacheService,
        PostNormalizer    $normalizer
    ) {
        $this->postService    = $postService;
        $this->commentService = $commentService;
        $this->cacheService   = $cacheService;
        $this->normalizer     = $normalizer;
    }

    public function lastPosts(int $numberOfPosts): JsonResponse
    {
        $posts = $this
            ->cacheService
            ->get(
                'last_posts', 
                10,
                sprintf('%s[]', Post::class),
                function () use ($numberOfPosts) {
                    return $this->postService->getLastPosts($numberOfPosts);
                }
            );
        $posts = $this->normalizer->normalizeArrayOfPosts($posts);

        return new JsonResponse($posts);
    }

    public function moreTalkedPosts(int $numberOfPosts): JsonResponse
    {
        $posts = $this
            ->cacheService
            ->get(
                'more_talked_posts', 
                10,
                sprintf('%s[]', Post::class),
                function () use ($numberOfPosts) {
                    return $this->postService->getMoreTalkedPosts($numberOfPosts);
                }
            );
        $posts = $this->normalizer->normalizeArrayOfPosts($posts);

        return new JsonResponse($posts);
    }
}