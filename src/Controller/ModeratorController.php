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
        PostService    $postService,
        CommentService $commentService
    ) {
        $this->postService    = $postService;
        $this->commentService = $commentService;
    }
    #[Route('', name: 'main')]
    public function main(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        return $this->render('moderator/moderator.html.twig');
    }

    #[Route('/posts/{numberOfPosts<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'posts')]
    public function showNotApprovedPosts(int $numberOfPosts, int $page): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $posts = $this
            ->postService
            ->getNotApprovedPosts($numberOfPosts, $page);

        return $this->render('moderator/mod_posts.html.twig', [
            'nameOfPath' => 'moderator_posts',
            'number'     => $numberOfPosts,
            'page'       => $page,
            'posts'      => $posts
        ]);
    }

    #[Route('/comments/{numberOfComments<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'comments')]
    public function showNotApprovedComments(int $numberOfComments, int $page): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        $comments = $this
            ->commentService
            ->getNotApprovedComments($numberOfComments, $page);

        return $this->render('moderator/mod_comments.html.twig', [
            'nameOfPath' => 'moderator_comments',
            'number'     => $numberOfComments,
            'page'       => $page,
            'comments'   => $comments
        ]);
    }

    #[Route('/post/{id}', name: 'post_show', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function showNotApprovedPost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('view', $post);

        return $this->renderForm('moderator/mod_post.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/approve/{id}', name: 'post_approve', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function approvePost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        if (! $post->getApprove()) {
            $this
                ->postService
                ->approve($post);
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
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');
        if (! $comment->getApprove()) {
            $this
                ->commentService
                ->approve($comment);
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
}