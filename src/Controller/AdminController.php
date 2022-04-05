<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\CommentService;
use App\Service\StabService;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private UserService $userService;
    private CommentService $commentService;
    private StabService $stabService;
    private CacheInterface $pool;
    private string $env;

    public function __construct(
        UserService $userService, 
        CommentService $commentService,
        StabService $stabService,
        CacheInterface $pool,
        KernelInterface $kernel
    ) {
        $this->userService = $userService;
        $this->commentService = $commentService;
        $this->stabService = $stabService;
        $this->pool = $pool;
        $this->env = $kernel->getEnvironment();
    }

    #[Route('', name: 'main')]
    public function showAdmin(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/comments/{numberOfComments<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'show_comments')]
    public function showComments(?int $numberOfComments, ?int $page): Response
    {
        $comments = $this->pool->get(sprintf('admin_comments_%s_%s', $numberOfComments, $page),
            function(ItemInterface $item) use ($numberOfComments, $page) {
                $item->expiresAfter(60);
                if ($page !== 1) {
                    $item->expiresAfter(3600);
                }
                $computedValue = $this->commentService->getComments($numberOfComments, $page);

                return $computedValue;
        });

        return $this->render('admin/allcomments.html.twig', [
            'nameOfPath' => 'admin_show_comments',
            'number' => $numberOfComments,
            'page' => $page,
            'comments' => $comments
        ]);
    }

    #[Route('/users/{numberOfUsers<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'show_users')]
    public function showUsers(?int $numberOfUsers, ?int $page): Response
    {
        $users = $this->pool->get(sprintf('admin_users_%s_%s', $numberOfUsers, $page),
            function(ItemInterface $item) use ($numberOfUsers, $page) {
                $item->expiresAfter(60);
                if ($page !== 1) {
                    $item->expiresAfter(3600);
                }
                $computedValue = $this->userService->getUsers($numberOfUsers, $page);

                return $computedValue;
        });

        return $this->render('admin/allusers.html.twig', [
            'nameOfPath' => 'admin_show_users',
            'number' => $numberOfUsers,
            'page' => $page,
            'users' => $users
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