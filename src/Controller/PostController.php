<?php
namespace App\Controller;

use App\Entity\Posts;
use App\Entity\AdditionalInfoPosts;
use App\Entity\RatingPosts;
use App\Repository\PostsRepository;
use App\Repository\RatingPostsRepository;
use App\Repository\AdditionalInfoPostsRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private $user, $isUser, $isAdmin, $maxSizeOfUploadImage = 4 * 1024 * 1024; // 4 megabytes

    public function __construct()
    {
        //$this->isUser = $this->isGranted('ROLE_USER');
        //$this->isAdmin = $this->isGranted('ROLE_ADMIN');
        // /** @var \App\Entity\User $user */
        // $this->user = $this->getUser();
        // if ($this->user)
        // {
        //     $this->isUser = true;
        //     if ($this->user->getRoles()[0] == 'ROLE_ADMIN')
        //     {
        //         $this->isAdmin = true;
        //     }
        // }
    }

    #[Route('', name: 'main', methods: ['GET'])]
    public function main(PostsRepository $postsRepository): Response
    {
        $numberOfPosts = 10;
        $numberOfMoreTalkedPosts = 3;
        $posts = $postsRepository->getLastPosts($numberOfPosts);
        $moreTalkedPosts = $postsRepository->getMoreTalkedPosts($numberOfMoreTalkedPosts);
        return $this->render('post/home.html.twig', [
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);
    }

    #[Route('/all', name: 'show_all', methods: ['GET'])]
    public function showAll(Request $request, PostsRepository $postsRepository): Response
    {
        $numberOfPosts = $request->query->get('number', 10);
        $page = $request->query->get('page', 1);
        $posts = $postsRepository->getPosts($numberOfPosts, $page);

        return $this->render('post/allposts.html.twig', [
            'nameOfPath' => 'post_show_all',
            'numberOfPosts' => $numberOfPosts,
            'page' => $page,
            'posts' => $posts
        ]);
    }

    #[Route('/{post_id}', name: 'show', methods: ['GET'], requirements: ['post_id' => '\b[0-9]+'])]
    public function showPost(
            int $post_id, 
            PostsRepository $postsRepository,
            RatingPostsRepository $ratingPostsRepository,
            CommentsRepository $commentsRepository
        ): Response
    {
        $post = $postsRepository->getPostById($post_id);
        if (!$post) {
            throw $this->createNotFoundException('Пост не найден');
        }
        $comments = $commentsRepository->findByPostId($post_id);
        $isUserAddRating = $ratingPostsRepository->findOneBy(
            [
                'user_id' => 0,
                'post_id' => $post_id
            ]
        );

        return $this->render('post/view.html.twig', [
            'post' => $post,
            'is_user_add_rating' => $isUserAddRating,
            'comments' => $comments
        ]);
    }

    #[Route('/add', name: 'show_add', methods: ['GET'])]
    public function showAdd(): Response
    {
        if (!$this->sessionUserId)
        {
            return $this->redirectToRoute('user_login');
        }
        return $this->render('post/add.html.twig', [
            'max_size_of_upload_image' => $this->maxSizeOfUploadImage
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $title = $request->request->get('title');
        $title = trim(strip_tags($title));
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $title && '' != $content && false != $this->sessionUserId)
        {
            $entityManager = $doctrine->getManager();
            $post = new Posts();
            $post->setTitle($title);
            $post->setUserId($this->sessionUserId);
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

    #[Route('/rating/{id}', name: 'rating', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
    public function addRating(
            int $id, 
            Request $request, 
            ManagerRegistry $doctrine,
            AdditionalInfoPostsRepository $additionalInfoPostsRepository,
            RatingPostsRepository $ratingPostsRepository
        )
    {
        if (!$this->sessionUserId)
        {
            return $this->redirectToRoute('user_login');
        }
        $rating = $request->request->get('rating', 0);
        $entityManager = $doctrine->getManager();

        $ratingPost = new RatingPosts();
        $ratingPost->setPostId($id);
        $ratingPost->setUserId($this->sessionUserId);
        $ratingPost->setRating($rating);
        $entityManager->persist($ratingPost);
        $entityManager->flush();

        $infoPost = $additionalInfoPostsRepository->find($id);
        $infoPost->setCountRatings($infoPost->getCountRatings() + 1);

        $generalRatingPost = (string) $ratingPostsRepository->countRating($id);
        $infoPost->setRating($generalRatingPost);
        $entityManager->flush();

        return $this->redirectToRoute('post_show', ['post_id' => $id]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\b[0-9]+'])]
    public function delete(Posts $posts, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();
        $entityManager->remove($posts);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Пост удален'
        );
        return $this->redirectToRoute('post_main');
    }
}