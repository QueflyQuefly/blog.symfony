<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Service\UserService;
use App\Service\CommentService;
use App\Service\StabService;
use App\Service\RedisCacheService;
use Symfony\Component\HttpKernel\KernelInterface;
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
    private RedisCacheService $cacheService;
    private string $env;

    public function __construct(
        UserService $userService, 
        CommentService $commentService,
        StabService $stabService,
        RedisCacheService $cacheService,
        KernelInterface $kernel
    ) {
        $this->userService = $userService;
        $this->commentService = $commentService;
        $this->stabService = $stabService;
        $this->cacheService = $cacheService;
        $this->env = $kernel->getEnvironment();
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/users/{numberOfUsers<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'show_users')]
    public function showUsers(?int $numberOfUsers, ?int $page): Response
    {
        $users = $this->cacheService->get(sprintf('admin_users_%s_%s', $numberOfUsers, $page), 60, sprintf('%s[]', User::class),
            function() use ($numberOfUsers, $page) {
                return $this->userService->getUsers($numberOfUsers, $page);
        });

        return $this->render('admin/allusers.html.twig', [
            'nameOfPath' => 'admin_show_users',
            'number'     => $numberOfUsers,
            'page'       => $page,
            'users'      => $users
        ]);
    }

    #[Route('/comments/{numberOfComments<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'show_comments')]
    public function showComments(?int $numberOfComments, ?int $page): Response
    {
        $comments = $this->cacheService->get(sprintf('admin_comments_%s_%s', $numberOfComments, $page), 60, sprintf('%s[]', Comment::class),
            function() use ($numberOfComments, $page) {
                return $this->commentService->getComments($numberOfComments, $page);
        });

        return $this->render('admin/allcomments.html.twig', [
            'nameOfPath' => 'admin_show_comments',
            'number'     => $numberOfComments,
            'page'       => $page,
            'comments'   => $comments
        ]);
    }

    #[Route('/stab', name: 'show_stab')]
    public function showStab(Request $request): Response
    {
        if ($this->env !== 'dev') {
            throw $this->createNotFoundException('Page not found');
        }
        $numberOfIterations = $request->query->get('number') ?? 0;
        $this->stabService->toStabDb($numberOfIterations);
        $errors = $this->stabService->getErrors() ?? false;

        return $this->render('admin/stab.html.twig', [
            'errors'             => $errors,
            'numberOfIterations' => $numberOfIterations
        ]);
    }

    #[Route('/users/delete/{id}', name: 'delete_user', requirements: ['id' => '(?!0)\b[0-9]+'])]
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