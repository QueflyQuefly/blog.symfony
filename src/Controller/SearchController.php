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
    )
    {
        $this->userService = $userService;
        $this->postService = $postService;
    }

    #[Route('/{search?}', name: 'posts', methods: ['GET'])]
    public function searchPosts(?string $search, Request $request): Response
    {
        $posts = false;
        if (!$search)
        {
            $search = $request->query->get('search');
        }
        $searchWords = (string) trim(strip_tags($search));
        if('' !== $searchWords)
        {
            $posts = $this->postService->searchPosts($searchWords);
        }
        return $this->render('search/searchposts.html.twig', [
            'search' => $search,
            'posts' => $posts
        ]);
    }

    #[Route('/users/{search?}', name: 'users', methods: ['GET'], priority:2)]
    public function searchUsers(?string $search, Request $request): Response
    {
        $users = false;
        if (!$search)
        {
            $search = $request->query->get('search');
        }
        $searchWords = (string) trim(strip_tags($search));
        if('' !== $searchWords)
        {
            $users = $this->userService->searchUsers($searchWords);
        }
        return $this->render('search/searchusers.html.twig', [
            'search' => $search,
            'users' => $users
        ]);
    }
}