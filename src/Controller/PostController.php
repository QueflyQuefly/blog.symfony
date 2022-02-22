<?php
namespace App\Controller;

use App\Entity\Posts;
use App\Repository\PostsRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/post', name: 'post_')]
class PostController extends AbstractController
{
    private $sessionUserId, $isSuperuser, $maxSizeOfUploadImage = 4 * 1024 * 1024; // 4 megabytes

    public function __construct(SessionInterface $sessionInterface)
    {
        $this->sessionUserId = $sessionInterface->get('user_id', false);
        $this->isSuperuser = $sessionInterface->get('is_superuser', false);
    }

    #[Route('', name: 'main', methods: ['GET'])]
    public function main(PostsRepository $postsRepository): Response
    {
        $amountOfPosts = 10;
        $amountOfMoreTalkedPosts = 3;
        $posts = $postsRepository->getLastPosts($amountOfPosts);
        $moreTalkedPosts = $postsRepository->getMoreTalkedPosts($amountOfMoreTalkedPosts);
        return $this->render('post/home.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser,
            'posts' => $posts,
            'moreTalkedPosts' => $moreTalkedPosts
        ]);
    }

    #[Route('/{post_id}', name: 'show', methods: ['GET'], requirements: ['post_id' => '\b[0-9]+'])]
    public function showPost(
            int $post_id, 
            PostsRepository $postsRepository,
            CommentsRepository $commentsRepository
        ): Response
    {
        $post = $postsRepository->find($post_id);
        $comments = $commentsRepository->findByPostId($post_id);
        
        if (!$post) {
            return $this->render('blog_message.html.twig', [
                'session_user_id' => $this->sessionUserId,
                'is_superuser' => $this->isSuperuser,
                'description' => "Пост №$post_id не найден"
            ]);
        }

        return $this->render('post/view.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser,
            'post' => $post,
            'comments' => $comments
        ]);
    }

    #[Route('/add', name: 'show_add', methods: ['GET'])]
    public function showAdd(): Response
    {
        if (!$this->sessionUserId)
        {
            return $this->redirectToRoute('user_show_login');
        }
        return $this->render('post/add.html.twig', [
            'session_user_id' => $this->sessionUserId,
            'is_superuser' => $this->isSuperuser,
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
    
    #[Route('/delete/{post_id}', name: 'delete', methods: ['POST'])]
    public function delete(Posts $posts, ManagerRegistry $doctrine): Response
    {
        if ($this->isSuperuser)
        {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($posts);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Пост удален'
            );
            return $this->redirectToRoute('post_main');
        }
        return $this->redirectToRoute('user_show_login');
    }
}