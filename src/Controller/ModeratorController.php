<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Service\PostService;
use App\Service\CommentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/moderator', name: 'moderator_')]
class ModeratorController extends AbstractController
{
    private PostService $postService;
    private CommentService $commentService;

    public function __construct(
        PostService $postService,
        CommentService $commentService
    ) {
        $this->postService = $postService;
        $this->commentService = $commentService;
    }
    #[Route('', name: 'main')]
    public function main(): Response
    {
        return $this->render('moderator/moderator.html.twig');
    }

    #[Route('/posts/{numberOfPosts<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'posts')]
    public function showNotApprovedPosts(int $numberOfPosts, int $page): Response
    {
        $posts = $this->postService->getNotApprovedPosts($numberOfPosts, $page);

        return $this->render('moderator/posts.html.twig', [
            'nameOfPath' => 'moderator_posts',
            'number'     => $numberOfPosts,
            'page'       => $page,
            'posts'      => $posts
        ]);
    }

    #[Route('/comments/{numberOfComments<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'comments')]
    public function showNotApprovedComments(int $numberOfComments, int $page): Response
    {
        $comments = $this->commentService->getNotApprovedComments($numberOfComments, $page);

        return $this->render('moderator/comments.html.twig', [
            'nameOfPath' => 'moderator_comments',
            'number'     => $numberOfComments,
            'page'       => $page,
            'comments'   => $comments
        ]);
    }

    #[Route('/post/{id}', name: 'post_show', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function showNotApprovedPost(int $id): Response
    {
        $post =  $this->postService->getNotApprovedPostById($id);

        if (!$post) {
            throw $this->createNotFoundException(sprintf('Пост с id = %s не найден. Вероятно, он удален', $id));
        }

        return $this->renderForm('moderator/viewpost.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/approve/{id}', name: 'post_approve', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function approvePost(Post $post): Response
    {
        if ($post) {
            $this->postService->approve($post);
            $this->addFlash(
                'success',
                sprintf('Пост №%s одобрен', $post->getId())
            );
        } else {
            $this->addFlash(
                'error',
                'Произошла ошибка'
            );
        }

        return $this->redirectToRoute('moderator_posts');
    }

    #[Route('/comment/approve/{id}', name: 'comment_approve', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function approveComment(Comment $comment): Response
    {
        if ($comment) {
            $this->commentService->approve($comment);
            $this->addFlash(
                'success',
                sprintf('Комментарий №%s одобрен', $comment->getId())
            );
        } else {
            $this->addFlash(
                'error',
                'Произошла ошибка'
            );
        }

        return $this->redirectToRoute('moderator_comments');
    }

    #[Route('/post/delete/{id}', name: 'post_delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function deletePost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $postId = $post->getId();
        $this->postService->delete($post);
        $this->addFlash(
            'success',
            sprintf('Пост №%s удален', $postId)
        );

        return $this->redirectToRoute('moderator_posts');
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function deleteComment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $commentId = $comment->getId();
        $this->commentService->delete($comment);
        $this->addFlash(
            'success',
            sprintf('Комментарий №%s удален', $commentId)
        );

        return $this->redirectToRoute('moderator_comments');
    }
}