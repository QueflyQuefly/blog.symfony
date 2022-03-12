<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Service\PostService;
use App\Service\CommentService;
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

    public function __construct(PostService $postService, CommentService $commentService)
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
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

    #[Route('/{postId}', name: 'show', requirements: ['postId' => '\b[0-9]+'])]
    public function showPost(int $postId, Request $request): Response
    {
        $post = $this->postService->getPostById($postId);
        if (!$post) {
            throw $this->createNotFoundException('Пост не найден');
        }
        $comments = $this->commentService->getCommentsByPostId($postId);
        $isUserAddRating = false;
        if ($userId = $this->getUserId())
        {
            $isUserAddRating = $this->postService->isUserAddRating($userId, $postId);
        }
        $tags = $this->postService->getTagsByPostId($postId);

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
            $content = $form->get('content')->getData();
            $this->commentService->create($userId, $postId, $content);
        }
        return $this->renderForm('post/view.html.twig', [
            'post' => $post,
            'is_user_add_rating' => $isUserAddRating,
            'tags' => $tags,
            'form' => $form,
            'comments' => $comments
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $this->getUserId();
            $title = $form->get('title')->getData();
            $content = $form->get('content')->getData();
            if ($this->postService->create($userId, $title, $content))
            {
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

    #[Route('/rating/{postId}', name: 'rating', methods: ['POST'], requirements: ['postId' => '\b[0-9]+'])]
    public function addRating(int $postId, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $userId = $this->getUserId();

        $rating = (int) $request->request->get('rating');
        $this->postService->addRating($userId, $postId, $rating);

        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\b[0-9]+'])]
    public function deletePost(Posts $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->postService->delete($post);
        $this->addFlash(
            'success',
            'Пост удален'
        );
        return $this->redirectToRoute('post_main');
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