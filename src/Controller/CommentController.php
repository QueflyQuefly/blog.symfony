<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    private CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    #[Route('/add/{postId}', name: 'add', methods: ['POST'], requirements: ['postId' => '\b[0-9]+'])]
    public function add(int $postId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $userId = $this->getUserId();
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $content)
        {
            $this->commentService->add($userId, $postId, $content);
            $this->addFlash(
                'success',
                'Комментарий добавлен'
            );
        } else {
            $this->addFlash(
                'error',
                'Произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    #[Route('/like/{postId}/{commentId}', name: 'like', methods: ['POST'], requirements: ['postId' => '\b[0-9]+', 'commentId' => '\b[0-9]+'])]
    public function like(int $postId, int $commentId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $userId = $this->getUserId();
        $this->commentService->like($userId, $commentId);
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }
    
    #[Route('/delete/{commentId}/{postId}', name: 'delete', methods: ['POST'], requirements: ['commentId' => '\b[0-9]+', 'postId' => '\b[0-9]+'])]
    public function delete(Comments $comment, int $postId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->commentService->delete($comment, $postId);
        $this->addFlash(
            'success',
            'Комментарий удален'
        );
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    private function getUserId(): ?int
    {
        if (!$this->isGranted('ROLE_USER'))
        {
            return null;
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        return $user->getId();
    }
}