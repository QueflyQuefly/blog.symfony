<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RedisCacheService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test', name: 'test_')]
class TestController extends AbstractController
{
    private RedisCacheService $cacheService;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;

    public function __construct(
        RedisCacheService $cacheService,
        UserService $userService,
        PostService $postService,
        CommentService $commentService
    ) {
        $this->cacheService = $cacheService;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
    }

    #[Route('/main', name: 'main')]
    public function main(): Response
    {
        $response = $this->render('blog_base.html.twig');

        return $response;
    }
}