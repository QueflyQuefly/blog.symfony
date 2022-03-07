<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\PostService;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private UserService $userService;
    private PostService $postService;
    private CommentService $commentService;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        UserService $userService,
        PostService $postService,
        CommentService $commentService
    )
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
    }

    #[Route('/profile/{userId<\b[0-9]+>?}', name: 'show_profile', methods: ['GET'])]
    public function showProfile(?int $userId): Response
    {
        if (!empty($userId)) {
            $user = $this->userService->find($userId);
            /** @var \App\Entity\User $sessionUser */
            if ($sessionUser = $this->getUser())
            {
                $canSubscribe = true;
                $isSubscribe = $this->userService->isSubscribe($sessionUser->getId(), $user->getId());
            }
        } else {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $canSubscribe = $isSubscribe = false;
        }
        $posts = $this->postService->getPostsByUserId($user->getId());
        $likedPosts = $this->postService->getLikedPostsByUserId($user->getId());
        $comments = $this->commentService->getCommentsByUserId($user->getId());
        $likedComments = $this->commentService->getLikedCommentsByUserId($user->getId());

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'can_subscribe' => $canSubscribe,
            'is_subscribe' => $isSubscribe,
            'posts' => $posts,
            'comments' => $comments,
            'likedPosts' => $likedPosts,
            'likedComments' => $likedComments,
        ]);
    }

    #[Route('/profile/subscribe/{userId<\b[0-9]+>}', name: 'subscribe', methods: ['POST'])]
    public function subscribe(int $userId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userIdWantSubscribe = $user->getid();

        $this->userService->subscribe($userIdWantSubscribe, $userId);

        return $this->redirectToRoute('user_show_profile', ['userId' => $userId]);
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