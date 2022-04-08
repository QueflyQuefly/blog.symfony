<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Service\PostService;
use App\Service\CommentService;
use App\Service\RedisCacheService;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private int $maxSizeOfUploadImage = 4194304; // 4 megabytes (4*1024*1024 bytes)
    private PostService $postService;
    private CommentService $commentService;
    private RedisCacheService $cacheService;

    public function __construct(
        PostService $postService,
        CommentService $commentService,
        RedisCacheService $cacheService
    ) {
        $this->postService = $postService;
        $this->commentService = $commentService;
        $this->cacheService = $cacheService;
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        $numberOfPosts = 10;
        $numberOfMoreTalkedPosts = 3;

        $posts = $this->cacheService->get('last_posts', 10, sprintf('%s[]', Post::class),
            function () use ($numberOfPosts) {
                return $this->postService->getLastPosts($numberOfPosts);
            }
        ); 

        $moreTalkedPosts = $this->cacheService->get('more_talked_posts', 10, sprintf('%s[]', Post::class),
            function () use ($numberOfMoreTalkedPosts) {
                return $this->postService->getMoreTalkedPosts($numberOfMoreTalkedPosts);
            }
        );

        $response = $this->render('post/home.html.twig', [
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);

        // $response->setEtag(md5($response->getContent()));
        // $response->setPublic();
        // $response->isNotModified($request);
        // $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/all/{numberOfPosts<(?!0)\b[0-9]+>?25}/{page<(?!0)\b[0-9]+>?1}', name: 'show_all')]
    public function showAll(int $numberOfPosts, int $page): Response
    {
        $posts = $this->cacheService->get(sprintf('all_posts_%s_%s', $numberOfPosts, $page), 10, sprintf('%s[]', Post::class),
            function () use ($numberOfPosts, $page) {
                return $this->postService->getPosts($numberOfPosts, $page);
        });

        return $this->render('post/allposts.html.twig', [
            'nameOfPath' => 'post_show_all',
            'number' => $numberOfPosts,
            'page' => $page,
            'posts' => $posts
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function showPost(int $id): Response
    {
        $post = $this->cacheService->get(sprintf('post_%s', $id), 10, Post::class,
            function () use ($id) {
                return $this->postService->getPostById($id);
            }
        );

        if (!$post) {
            throw $this->createNotFoundException(sprintf('Пост с id = %s не найден. Вероятно, он удален', $id));
        }
        $comments = $this->cacheService->get(sprintf('comments_post_%s', $id), 10, sprintf('%s[]', Comment::class),
            function () use ($id) {
                return $this->commentService->getCommentsByPostId($id);
            }
        );

        $form = $this->createForm(CommentFormType::class);

        return $this->renderForm('post/view.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $title = $form->get('title')->getData();
            $content = $form->get('content')->getData();
            if ($this->postService->create($user, $title, $content)) {
                $this->addFlash(
                    'success',
                    'Пост добавлен'
                );
            } else {
                $this->addFlash(
                    'error',
                    'При добавлении поста произошла ошибка'
                );
            }
            return $this->redirectToRoute('post_main');
        }
        return $this->renderForm('post/add.html.twig', [
            'form' => $form,
            'max_size_of_upload_image' => $this->maxSizeOfUploadImage
        ]);
    }

    #[Route('/rating/{id}', name: 'rating', methods: ['POST'], requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function addRating(Post $post, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $user = $this->getUser();
        $rating = (int) $request->request->get('rating');
        if ($this->postService->addRating($user, $post, $rating)) {
            $this->addFlash(
                'success',
                'Ваша оценка принята'
            );
        } else {
            $this->addFlash(
                'error',
                'Вы уже оставили оценку для этого поста'
            );
        }

        return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '(?!0)\b[0-9]+'])]
    public function deletePost(Post $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->postService->delete($post);
        $this->addFlash(
            'success',
            'Пост удален'
        );
        return $this->redirectToRoute('post_main');
    }
}