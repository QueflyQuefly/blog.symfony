<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\AdditionalInfoPosts;
use App\Service\PostService;
use App\Repository\PostsRepository;
use App\Repository\RatingPostsRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private int $maxSizeOfUploadImage = 4194304; // 4 megabytes (4*1024*1024 bytes)
    private ManagerRegistry $doctrine;
    private PostsRepository $postsRepository;
    private RatingPostsRepository $ratingPostsRepository;
    private CommentsRepository $commentsRepository;
    private PostService $postService;

    public function __construct(      
        ManagerRegistry $doctrine,
        PostsRepository $postsRepository,
        RatingPostsRepository $ratingPostsRepository,
        CommentsRepository $commentsRepository,
        PostService $postService
    )
    {
        $this->postsRepository = $postsRepository;
        $this->ratingPostsRepository = $ratingPostsRepository;
        $this->commentsRepository = $commentsRepository;
        $this->doctrine = $doctrine;
        $this->postService = $postService;
    }

    #[Route('', name: 'main', methods: ['GET'])]
    public function main(): Response
    {
        $numberOfPosts = 10;
        $numberOfMoreTalkedPosts = 3;
        $posts = $this->postsRepository->getLastPosts($numberOfPosts);
        $moreTalkedPosts = $this->postsRepository->getMoreTalkedPosts($numberOfMoreTalkedPosts);
        return $this->render('post/home.html.twig', [
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);
    }

    #[Route('/all', name: 'show_all', methods: ['GET'])]
    public function showAll(Request $request): Response
    {
        $numberOfPosts = (int) $request->query->get('number', 10);
        $page = (int) $request->query->get('page', 1);
        $posts = $this->postsRepository->getPosts($numberOfPosts, $page);
        return $this->render('post/allposts.html.twig', [
            'nameOfPath' => 'post_show_all',
            'numberOfPosts' => $numberOfPosts,
            'page' => $page,
            'posts' => $posts
        ]);
    }

    #[Route('/{postId}', name: 'show', methods: ['GET'], requirements: ['postId' => '\b[0-9]+'])]
    public function showPost(int $postId): Response
    {
        $post = $this->postsRepository->getPostById($postId);
        if (!$post) {
            throw $this->createNotFoundException('Пост не найден');
        }
        $comments = $this->commentsRepository->findByPostId($postId);
        $isUserAddRating = false;
        if ($sessionUserId = $this->getUserId())
        {
            $isUserAddRating = $this->ratingPostsRepository->findOneBy(
                [
                    'userId' => $sessionUserId,
                    'postId' => $postId
                ]
            );
        }
        return $this->render('post/view.html.twig', [
            'post' => $post,
            'is_user_add_rating' => $isUserAddRating,
            'comments' => $comments
        ]);
    }

    #[Route('/add', name: 'show_add', methods: ['GET'])]
    public function showAdd(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        return $this->render('post/add.html.twig', [
            'max_size_of_upload_image' => $this->maxSizeOfUploadImage
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sessionUserId = $this->getUserId();

        $title = $request->request->get('title');
        $title = trim(strip_tags($title));
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $title && '' != $content)
        {
            $entityManager = $this->doctrine->getManager();
            $post = new Posts();
            $post->setTitle($title);
            $post->setUserId($sessionUserId);
            $post->setContent($content);
            $post->setDateTime(time());
            $entityManager->persist($post);
            $entityManager->flush();

            $postInfo = new AdditionalInfoPosts();
            $postInfo->setRating('0.0');
            $postInfo->setPostId($post->getId());
            $postInfo->setCountComments(0);
            $postInfo->setCountRatings(0);
            $entityManager->persist($postInfo);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Пост добавлен'
            );
            return $this->redirectToRoute('post_main');
        } else {
            $this->addFlash(
                'error',
                'При добавлении поста произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show_add');
    }

    #[Route('/rating/{postId}', name: 'rating', methods: ['POST'], requirements: ['postId' => '\b[0-9]+'])]
    public function addRating(int $postId, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sessionUserId = $this->getUserId();

        $rating = (int) $request->request->get('rating');
        $this->postService->addRating($postId, $rating, $sessionUserId);

        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
    public function delete(Posts $posts): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($posts);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Пост удален'
        );
        return $this->redirectToRoute('post_main');
    }

    private function getUserId(): int
    {
        if (!$this->isGranted('ROLE_USER'))
        {
            return 0;
        }
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        return $user->getId();
    }
}