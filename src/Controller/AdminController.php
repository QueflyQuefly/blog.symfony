<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\CommentService;
use App\Service\StabService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private UserService $userService;
    private CommentService $commentService;
    private StabService $stabService;

    public function __construct(
        UserService $userService, 
        CommentService $commentService,
        StabService $stabService
    )
    {
        $this->userService = $userService;
        $this->commentService = $commentService;
        $this->stabService = $stabService;
    }

    #[Route('', name: 'main', methods: ['GET'])]
    public function showAdmin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/comments/{numberOfComments<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_comments', methods: ['GET'])]
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

    #[Route('/users/{numberOfUsers<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_users', methods: ['GET'])]
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

    #[Route('/stab', name: 'show_stab', methods: ['GET'])]
    public function showStab(Request $request): Response
    {
        $numberOfIterations = $request->query->get('number') ?? 0;
        $this->stabService->toStabDb($numberOfIterations);
        $errors = $this->stabService->getErrors() ?? false;
        return $this->render('admin/stab.html.twig', [
            'errors' => $errors,
            'numberOfIterations' => $numberOfIterations
        ]);
    }

    #[Route('/users/delete/{id}', name: 'delete_user', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
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