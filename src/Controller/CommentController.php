<?php
namespace App\Controller;

use App\Entity\Comments;
use App\Repository\AdditionalInfoPostsRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    private $user, $isUser, $isAdmin;

    public function __construct()
    {
        $this->isUser = $this->isGranted('ROLE_USER');
        $this->isAdmin = $this->isGranted('ROLE_ADMIN');
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

    #[Route('/add/{post_id}', name: 'add', methods: ['POST'])]
    public function add(int $post_id, Request $request, ManagerRegistry $doctrine, AdditionalInfoPostsRepository $additionalInfoPostsRepository): Response
    {
        if (false == $this->isUser)
        {
            return $this->redirectToRoute('user_login');
        }
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $content)
        {
            $entityManager = $doctrine->getManager();

            $comment = new Comments();
            $comment->setPostId($post_id);
            $comment->setUserId(3); //fffffffffffffffffffffffffffffffffffffff
            $comment->setDateTime(time());
            $comment->setContent($content);
            $comment->setRating(0);
            $entityManager->persist($comment);
            $entityManager->flush();

            $infoPost = $additionalInfoPostsRepository->find($post_id);
            $infoPost->setCountComments($infoPost->getCountComments() + 1);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Комментарий добавлен'
            );
            return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
        } else {
            $this->addFlash(
                'error',
                'При добавлении комментария произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
    }

    #[Route('/like/{post_id}/{comment_id}', name: 'like', methods: ['POST'])]
    public function like(int $post_id, int $comment_id, CommentsRepository $commentsRepository, ManagerRegistry $doctrine): Response
    {
        if ($this->sessionUserId)
        {
            $commentsRepository->like($comment_id, $this->sessionUserId, $doctrine);
            return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
        }
        return $this->redirectToRoute('user_login');
    }
    
    #[Route('/delete/{post_id}/{comment_id}', name: 'delete', methods: ['POST'])]
    public function delete(int $post_id, Comments $comments, ManagerRegistry $doctrine, AdditionalInfoPostsRepository $additionalInfoPostsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $doctrine->getManager();
        $entityManager->remove($comments);
        $entityManager->flush();

        $infoPost = $additionalInfoPostsRepository->find($post_id);
        $infoPost->setCountComments($infoPost->getCountComments() - 1);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Комментарий удален'
        );
        return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
    }
}