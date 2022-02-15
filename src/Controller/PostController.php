<?php
namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post", name="post_", methods={"GET"})
 */
class PostController extends AbstractController
{
    /**
     * @Route("", name="main", methods={"GET"})
     */
    public function main(PostsRepository $postsRepository): Response
    {
        $numberOfPosts = 10;
        $posts = $postsRepository->getLastPostsByNumber($numberOfPosts);
        $sessionUserId= 1;
        $isSuperuser = true;
        return $this->render('post/home.html.twig', [
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'posts' => $posts,
            'post' => [
                0 => [
                    'post_id' => 2,
                    'title' => 1,
                    'user_id' => 1,
                    'date_time' => 1640351274,
                    'content' => 1,
                    'rating' => 0.0,
                    'count_comments' => 1,
                    'count_ratings' => 0,
                    'author' => 'Администратор'
                ],
                1 => [
                    'post_id' => 4,
                    'title' => 1,
                    'user_id' => 1,
                    'date_time' => 1640351274,
                    'content' => 1,
                    'rating' => 0.0,
                    'count_comments' => 1,
                    'count_ratings' => 0,
                    'author' => 'Администратор'
                ]
            ],
            'class' => 'viewpost'
        ]);
    }
    /**
     * @Route("/{post_id}", name="show", methods={"GET"}, requirements={"post_id"="\b[0-9]+"})
     */
    public function showPost(int $post_id, PostsRepository $postsRepository): Response
    {
        $post = $postsRepository->find($post_id);

        if (!$post) {
            $pageDescription = 'No post found for id '.$post_id;
        } else {
            $pageDescription = 'Check out this great product: '.$post->getTitle();
        }

        $pageTitle = "Пост $post_id - Просто Блог";
        $sessionUserId = 1;
        $isSuperuser = true;
        return $this->render('post/view.html.twig', [
            'title' => $pageTitle,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'post' => $post,
            'description' => $pageDescription
        ]);
    }
    /**
     * @Route("/add", name="show_add", methods={"GET"})
     */
    public function showAdd(): Response
    {
        $pageTitle = 'Создание поста - Просто Блог';
        $maxSizeOfUploadImage = 4 * 1024 * 1024; // 4 megabytes
        $sessionUserId = 1;
        $isSuperuser = true;
        return $this->render('post/add.html.twig', [
            'title' => $pageTitle,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'max_size_of_upload_image' => $maxSizeOfUploadImage
        ]);
    }
    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $title = $request->get('title');
        $content = $request->get('content');
        $sessionUserId = 1;
        $entityManager = $doctrine->getManager();

        $post = new Posts();
        $post->setTitle($title);
        $post->setUserId($sessionUserId);
        $post->setContent($content);
        $post->setDateTime(time());
        // tell Doctrine you want to (eventually) save the Posts (no queries yet)
        $entityManager->persist($post);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        //return new Response('Saved new posts with id '.$posts->getId());

        $isSuperuser = true;
        return $this->render('blog_message.html.twig', [
            'title' => $title,
            'sessionUserId' => $sessionUserId,
            'isSuperuser' => $isSuperuser,
            'description' => 'Пост создан',
            'referrer' => 'post_show_add'
        ]);
    }
}