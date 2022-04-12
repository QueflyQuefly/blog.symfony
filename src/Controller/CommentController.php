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

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    #[Route('/add/{id}', name: 'add', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function create(Post $post, Request $request): Response
    {
        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        if (!$post->getApprove()) {
            throw $this->createNotFoundException('Невозможно добавить комментарий к неодобренному посту');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $content = $form->get('content')->getData();
            $approve = false;
            if ($this->isGranted('ROLE_MODERATOR')) {
                $approve = true;
            }
            if ($this->commentService->create($user, $post, $content, $approve)) {
                if ($approve) {
                    $this->addFlash(
                        'success',
                        'Ваш комментарий добавлен'
                    );
                } else {
                    $this->addFlash(
                        'success',
                        'Ваш комментарий отправлен на модерацию'
                    );
                }
            }
        } else {
            $this->addFlash(
                'error',
                'Заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/like/{postId<(?!0)\b[0-9]+>}/{id<(?!0)\b[0-9]+>}', name: 'like')]
    public function like(int $postId, Comment $comment): Response
    {
        if (!$comment->getApprove()) {
            throw $this->createNotFoundException('Невозможно поставить лайк неодобренному комментарию');
        }
        $user = $this->getUser();
        $this->commentService->like($user, $comment);

        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function update(Comment $comment, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $postId = ($comment->getPost())->getId();

        if (
            $this->isGranted('ROLE_ADMIN') 
            || ($this->isGranted('ROLE_MODERATOR') && !$comment->getApprove()) 
            || $user->getId() === $comment->getUser()->getId()
        ) {
            $form = $this->createForm(CommentFormType::class, $comment, [
                'content' => $comment->getContent(),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $comment = $form->getData();
                if ($this->isGranted('ROLE_MODERATOR')) {
                    $comment->setApprove(true);
                }
                $this->commentService->update($comment);

                if ($this->isGranted('ROLE_ADMIN') || $user->getId() === $comment->getUser()->getId()) {
                    return $this->redirectToRoute('post_show', ['id' => $postId]);
                } else {
                    return $this->redirectToRoute('moderator_comments');
                }
            }

            return $this->renderForm('comment/comment_update.html.twig', [
                'form' => $form
            ]);
        } else {
            throw $this->createNotFoundException('Something went wrong');
        }
    }
    
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function deleteComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $postId = ($comment->getPost())->getId();
        $commentId = $comment->getId();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            
            $this->commentService->delete($comment);
            $this->addFlash(
                'success',
                sprintf('Комментарий №%s удален', $commentId)
            );

            return $this->redirectToRoute('post_show', ['id' => $postId]);
        } elseif ($this->isGranted('ROLE_MODERATOR') && !$comment->getApprove()) {
            $this->commentService->delete($comment);
            $this->addFlash(
                'success',
                sprintf('Комментарий №%s удален', $commentId)
            );

            return $this->redirectToRoute('moderator_comments');
        } elseif ($user->getId() === $comment->getUser()->getId()) {
            $this->commentService->delete($comment);
            $this->addFlash(
                'success',
                sprintf('Комментарий №%s удален', $commentId)
            );

            return $this->redirectToRoute('user_show_profile', [
                'id' => $user->getId()
            ]);
        } else {
            throw $this->createNotFoundException('Something went wrong');
        }
    }
}