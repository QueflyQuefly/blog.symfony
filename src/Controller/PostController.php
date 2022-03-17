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

#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private int $maxSizeOfUploadImage = 4194304; // 4 megabytes (4*1024*1024 bytes)
    private PostService $postService;

    public function __construct(
        PostService $postService
    ) {
        $this->postService = $postService;
    }

    #[Route('', name: 'main')]
    public function main(): Response
    {
        $numberOfPosts = 10;
        $numberOfMoreTalkedPosts = 3;
        $posts = $this->postService->getLastPosts($numberOfPosts);
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfMoreTalkedPosts);
        return $this->render('post/home.html.twig', [
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);
    }

    #[Route('/all/{numberOfPosts<\b[0-9]+>?25}/{page<\b[0-9]+>?1}', name: 'show_all')]
    public function showAll(?int $numberOfPosts, ?int $page): Response
    {
        $posts = $this->postService->getPosts($numberOfPosts, $page);
        return $this->render('post/allposts.html.twig', [
            'nameOfPath' => 'post_show_all',
            'number' => $numberOfPosts,
            'page' => $page,
            'posts' => $posts
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\b[0-9]+'])]
    public function showPost(Post $post): Response
    {
        if (!$post) {
            throw $this->createNotFoundException('Пост не найден');
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $form = $this->createForm(CommentFormType::class);
        $isUserAddRating = false;
        if ($user) {
            $isUserAddRating = $this->postService->isUserAddRating($user, $post);
        }
        return $this->renderForm('post/view.html.twig', [
            'post' => $post,
            'is_user_add_rating' => $isUserAddRating,
            'form' => $form,
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
        $this->postService->addRating($user, $post, $rating);
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