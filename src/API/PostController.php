<?php

namespace App\API;

use App\Entity\Post;
use App\Entity\Comment;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RedisCacheService;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    public const MAX_SIZE_OF_IMAGE = 4194304; // 4 megabytes (4*1024*1024 bytes)

    private PostService $postService;

    private CommentService $commentService;

    private RedisCacheService $cacheService;

    public function __construct(
        PostService       $postService,
        CommentService    $commentService,
        RedisCacheService $cacheService
    ) {
        $this->postService    = $postService;
        $this->commentService = $commentService;
        $this->cacheService   = $cacheService;
    }

    public function main(): Response
    {
        $numberOfPosts = 10;
        $posts         = $this
            ->cacheService
            ->getWithoutSerializer(
                'last_posts', 
                10,
                function () use ($numberOfPosts) {
                    return json_encode($this->postService->getLastPostsAsArrays($numberOfPosts));
                }
            );

        return new Response($posts);
    }
}