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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user->getIsBanned() > time()) {
            $text = sprintf('Вы забанены. <br> Доступ будет восстановлен %s', date('d.m.Y в H:i', $user->getIsBanned()));

            return $this->render('blog_message.html.twig', [
                'description' => $text
            ]);
        }

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        if (! $post->getApprove()) {
            throw $this->createNotFoundException('Невозможно добавить комментарий к неодобренному посту');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $approve = false;
            $content = $form
                ->get('content')
                ->getData();

            if ($this->isGranted('ROLE_MODERATOR')) {
                $approve = true;
            }

            $comment = $this->commentService->create($user, $post, $content, $approve);

            if (! empty($comment)) {
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
            } else {
                $this->addFlash(
                    'error',
                    'Произошла ошибка при отправке комментария'
                );
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (! $comment->getApprove()) {
            throw $this->createNotFoundException('Невозможно поставить лайк неодобренному комментарию');
        }

        $user = $this->getUser();
        $this
            ->commentService
            ->changeLike($user, $comment);

        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function editComment(Comment $comment, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $comment);
        /** @var \App\Entity\User $user */
        $user   = $this->getUser();
        $postId = ($comment->getPost())->getId();

        $form = $this->createForm(CommentFormType::class, $comment, [
            'content' => $comment->getContent(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
    
            if ($this->isGranted('ROLE_MODERATOR')) {
                $comment->setApprove(true);
            }

            $this
                ->commentService
                ->update();

            if ($this->isGranted('ROLE_ADMIN') || $user->getId() === $comment->getUser()->getId()) {
                return $this->redirectToRoute('post_show', ['id' => $postId]);
            } else {
                return $this->redirectToRoute('moderator_comments');
            }
        }

        return $this->renderForm('comment/comment_edit.html.twig', [
            'form' => $form
        ]);
    }
    
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function deleteComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('delete', $comment);
        $postId = $comment
            ->getPost()
            ->getId();
        $commentId = $comment->getId();

        $this
            ->commentService
            ->delete($comment);
        $this->addFlash(
            'success',
            sprintf('Комментарий №%s удален', $commentId)
        );

        if (! $comment->getApprove()) {
            return $this->redirectToRoute('moderator_comments');
        }

        return $this->redirectToRoute('post_show', ['id' => $postId]);
    }
}