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
use Symfony\Component\HttpKernel\KernelInterface;

#[Route('/test', name: 'test_')]
class TestController extends AbstractController
{
    private RedisCacheService $cacheService;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;
    private string $env;

    public function __construct(
        RedisCacheService $cacheService,
        UserService $userService,
        PostService $postService,
        CommentService $commentService,
        KernelInterface $kernel
    ) {
        $this->cacheService = $cacheService;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->env = $kernel->getEnvironment();
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        if ($this->env !== 'dev') {
            throw $this->createNotFoundException('Access denied');
        }

        
        $response = $this->render('blog_base.html.twig');

        return $response;
    }
}