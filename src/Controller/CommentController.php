<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\CommentService;
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

    #[Route('/like/{postId}/{id}', name: 'like', requirements: ['postId' => '\b[0-9]+', 'commentId' => '\b[0-9]+'])]
    public function like(int $postId, Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $user = $this->getUser();
        $this->commentService->like($user, $comment);
        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }
    
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\b[0-9]+'])]
    public function deleteComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $postId = ($comment->getPost())->getId();
        $this->commentService->delete($comment, $postId);
        $this->addFlash(
            'success',
            'Комментарий удален'
        );
        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }
}