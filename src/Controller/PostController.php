<?php
namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post", name="post_", methods={"GET"})
 */
class PostController extends AbstractController
{
    /**
     * @Route("", name="main", methods={"GET"})
     */
    public function main(PostsRepository $postsRepository, SessionInterface $sessionInterface): Response
    {
        $amountOfPosts = 10;
        $amountOfMoreTalkedPosts = 3;
        $posts = $postsRepository->getLastPosts($amountOfPosts);
        $moreTalkedPosts = $postsRepository->getMoreTalkedPosts($amountOfMoreTalkedPosts);
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        return $this->render('post/home.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);
    }

    /**
     * @Route("/{post_id}", name="show", methods={"GET"}, requirements={"post_id"="\b[0-9]+"})
     */
    public function showPost(int $post_id, PostsRepository $postsRepository, SessionInterface $sessionInterface): Response
    {
        $post = $postsRepository->find($post_id);

        if (!$post) {
            $pageDescription = 'No post found for id '.$post_id;
        } else {
            $pageDescription = 'Check out this great product: '.$post->getTitle();
        }
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        return $this->render('post/view.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'post' => $post,
            'description' => $pageDescription
        ]);
    }

    /**
     * @Route("/add", name="show_add", methods={"GET"})
     */
    public function showAdd(SessionInterface $sessionInterface): Response
    {
        $maxSizeOfUploadImage = 4 * 1024 * 1024; // 4 megabytes
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
        return $this->render('post/add.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'max_size_of_upload_image' => $maxSizeOfUploadImage
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"POST"})
     */
    public function add(Request $request, ManagerRegistry $doctrine, SessionInterface $sessionInterface): Response
    {
        $title = $request->get('title');
        $title = trim(strip_tags($title));
        $content = $request->get('content');
        $content = trim(strip_tags($content));
        $sessionUserId = $sessionInterface->get('user_id');
        $isSuperuser = $sessionInterface->get('is_superuser');
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

        return $this->render('blog_message.html.twig', [
            'session_user_id' => $sessionUserId,
            'is_superuser' => $isSuperuser,
            'description' => 'Пост создан',
            'referrer' => 'post_show_add'
        ]);
    }
}