<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PostsRepository;
use App\Repository\CommentsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private UserRepository $userRepository;
    private PostsRepository $postsRepository;
    private CommentsRepository $commentsRepository;

    public function __construct(      
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepository,
        PostsRepository $postsRepository,
        CommentsRepository $commentsRepository
    )
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->userRepository = $userRepository;
        $this->postsRepository = $postsRepository;
        $this->commentsRepository = $commentsRepository;
    }

    #[Route('/profile/{userId<\b[0-9]+>?}', name: 'show_profile', methods: ['GET'])]
    public function showProfile(?int $userId, Request $request): Response
    {
        if (!empty($userId)) {
            $user = $this->userRepository->find($userId);
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
        }
        $posts = $this->postsRepository->getPostsByUserId($user->getId());
        $comments = $this->commentsRepository->findByUserId($user->getId());
        $likedPosts = $this->postsRepository->getLikedPostsByUserId($user->getId());
        $likedComments = $this->commentsRepository->getLikedCommentsByUserId($user->getId());

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'comments' => $comments,
            'likedPosts' => $likedPosts,
            'likedComments' => $likedComments,
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout()
    {
        // controller can be blank: it will never be called!
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}