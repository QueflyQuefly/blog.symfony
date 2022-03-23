<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Service\CommentService;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    private CommentService $commentService;

    public function __construct(
        CommentService $commentService
    ) {
        $this->commentService = $commentService;
    }

    #[Route('/add/{id}', name: 'add', requirements: ['id' => '\b[0-9]+'])]
    public function create(Post $post, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $content = $form->get('content')->getData();
            $this->commentService->create($user, $post, $content);
        } else {
            $this->addFlash(
                'error',
                'Заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
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
        $this->commentService->delete($comment);
        $this->addFlash(
            'success',
            'Комментарий удален'
        );
        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }
}