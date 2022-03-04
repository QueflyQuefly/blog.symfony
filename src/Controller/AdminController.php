<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private UserService $userService;
    private CommentService $commentService;

    public function __construct(UserService $userService, CommentService $commentService)
    {
        $this->userService = $userService;
        $this->commentService = $commentService;
    }

    #[Route('', name: 'main', methods: ['GET'])]
    public function showAdmin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/comments/{numberOfComments<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_comments', methods: ['GET'])]
    public function showComments(?int $numberOfComments, ?int $page): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $comments = $this->commentService->getComments($numberOfComments, $page);
        return $this->render('admin/allcomments.html.twig', [
            'nameOfPath' => 'admin_show_comments',
            'number' => $numberOfComments,
            'page' => $page,
            'comments' => $comments
        ]);
    }

    #[Route('/users/{numberOfUsers<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_users', methods: ['GET'])]
    public function showUsers(?int $numberOfUsers, ?int $page): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $this->userService->getUsers($numberOfUsers, $page);
        return $this->render('admin/allusers.html.twig', [
            'nameOfPath' => 'admin_show_users',
            'number' => $numberOfUsers,
            'page' => $page,
            'users' => $users
        ]);
    }

    #[Route('/users/delete/{id}', name: 'delete_user', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
    public function deleteUser(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->userService->delete($user);
        $this->addFlash(
            'success',
            'Пользователь удален'
        );
        return $this->redirectToRoute('admin_show_users');
    }
}