<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private UserService $userService;
    private CommentService $commentService;

    public function __construct(
        UserService $userService, 
        CommentService $commentService
    )
    {
        $this->userService = $userService;
        $this->commentService = $commentService;
    }

    #[Route('', name: 'main')]
    public function showAdmin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/comments/{numberOfComments<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_comments')]
    public function showComments(?int $numberOfComments, ?int $page): Response
    {
        $comments = $this->commentService->getComments($numberOfComments, $page);
        return $this->render('admin/allcomments.html.twig', [
            'nameOfPath' => 'admin_show_comments',
            'number' => $numberOfComments,
            'page' => $page,
            'comments' => $comments
        ]);
    }

    #[Route('/users/{numberOfUsers<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_users')]
    public function showUsers(?int $numberOfUsers, ?int $page): Response
    {
        $users = $this->userService->getUsers($numberOfUsers, $page);
        return $this->render('admin/allusers.html.twig', [
            'nameOfPath' => 'admin_show_users',
            'number' => $numberOfUsers,
            'page' => $page,
            'users' => $users
        ]);
    }

    #[Route('/users/delete/{id}', name: 'delete_user', requirements: ['id' => '\b[0-9]+'])]
    public function deleteUser(User $user): Response
    {
        $this->userService->delete($user);
        $this->addFlash(
            'success',
            'Пользователь удален'
        );
        return $this->redirectToRoute('admin_show_users');
    }
}