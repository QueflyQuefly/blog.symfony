<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PostService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/search', name: 'search_')]
class SearchController extends AbstractController
{
    private UserService $userService;
    private PostService $postService;

    public function __construct(
        UserService $userService,
        PostService $postService
    ) {
        $this->userService = $userService;
        $this->postService = $postService;
    }

    #[Route('/{search?}', name: 'posts')]
    public function searchPosts(?string $search, Request $request): Response
    {
        $posts = false;
        if (!$search) {
            $search = (string) $request->query->get('search');
        }
        $searchWords = trim(strip_tags($search));
        if('' !== $searchWords) {
            $numberOfResults = 80;
            $posts = $this->postService->searchPosts($searchWords, $numberOfResults);
        }

        return $this->render('search/search_posts.html.twig', [
            'search' => $search,
            'posts'  => $posts
        ]);
    }

    #[Route('/users/{search?}', name: 'users', priority:2)]
    public function searchUsers(?string $search, Request $request): Response
    {
        $users = false;
        if (!$search) {
            $search = $request->query->get('search');
        }
        $searchWords = (string) trim(strip_tags($search));
        if('' !== $searchWords) {
            $users = $this->userService->searchUsers($searchWords);
        }

        return $this->render('search/search_users.html.twig', [
            'search' => $search,
            'users'  => $users
        ]);
    }
}