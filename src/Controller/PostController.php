<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\PostService;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private int $maxSizeOfUploadImage = 4194304; // 4 megabytes (4*1024*1024 bytes)
    private PostService $postService;
    private CacheInterface $pool;

    public function __construct(
        PostService $postService,
        CacheInterface $pool
    ) {
        $this->postService = $postService;
        $this->pool = $pool;
        /* $client = RedisAdapter::createConnection(
            'redis://localhost',
            [
                'persistent'     => 0,
                'persistent_id'  => null,
                'timeout'        => 30,
                'read_timeout'   => 0,
                'retry_interval' => 0,
            ]
        ); */
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        $posts = $this->pool->get('last_posts', function (ItemInterface $item) {
            $item->expiresAfter(60);
            $numberOfPosts = 10;
            $computedValue = $this->postService->getLastPosts($numberOfPosts);

            return $computedValue;
        });
        $moreTalkedPosts = $this->pool->get('more_talked_posts', function (ItemInterface $item) {
            $item->expiresAfter(60);
            $numberOfMoreTalkedPosts = 3;
            $computedValue = $this->postService->getMoreTalkedPosts($numberOfMoreTalkedPosts);

            return $computedValue;
        });

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

    #[Route('/all/{numberOfPosts<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_all')]
    public function showAll(int $numberOfPosts, int $page): Response
    {
        if ($numberOfPosts === 0 || $page === 0) {
            throw $this->createNotFoundException('Кол-во постов или страница не может быть равна 0');
        }
        $posts = $this->postService->getPosts($numberOfPosts, $page);

        /* $posts = $this->pool->get("all_posts_$numberOfPosts-$page" , function (ItemInterface $item) {
            $item->expiresAfter(60);
            if ($GLOBALS['page'] !== 1) {
                $item->expiresAfter(3600);
            }
            $computedValue = $this->postService->getPosts($GLOBALS['numberOfPosts'], $GLOBALS['page']);

            return $computedValue;
        }); */

        return $this->render('post/allposts.html.twig', [
            'nameOfPath' => 'post_show_all',
            'number' => $numberOfPosts,
            'page' => $page,
            'posts' => $posts
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\b[0-9]+'])]
    public function showPost(int $id): Response
    {
        if (!$post = $this->postService->getPostById($id)) {
            throw $this->createNotFoundException('Пост не найден. Вероятно запрашиваемая информация была удалена');
        }
        $form = $this->createForm(CommentFormType::class);

        return $this->renderForm('post/view.html.twig', [
            'post' => $post,
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

    #[Route('/rating/{id}', name: 'rating', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
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

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\b[0-9]+'])]
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