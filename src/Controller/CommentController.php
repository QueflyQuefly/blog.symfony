<?php
namespace App\Controller;

use App\Entity\Comments;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    private $sessionUserId, $isSuperuser;

    public function __construct(SessionInterface $sessionInterface)
    {
        $this->sessionUserId = $sessionInterface->get('user_id', false);
        $this->isSuperuser = $sessionInterface->get('is_superuser', false);
    }

    #[Route('/add/{post_id}', name: 'add', methods: ['POST'])]
    public function add(int $post_id, Request $request, ManagerRegistry $doctrine): Response
    {
        if (false == $this->sessionUserId)
        {
            return $this->redirectToRoute('user_show_login');
        }
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $content)
        {
            $entityManager = $doctrine->getManager();
            $comment = new Comments();
            $comment->setPostId($post_id);
            $comment->setUserId($this->sessionUserId);
            $comment->setDateTime(time());
            $comment->setContent($content);
            $comment->setRating(0);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
        } else {
            $this->addFlash(
                'error',
                'При добавлении комментария произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
    }
    
    #[Route('/delete/{post_id}/{comment_id}', name: 'delete', methods: ['POST'])]
    public function delete(int $post_id, Comments $comments, ManagerRegistry $doctrine): Response
    {
        if ($this->isSuperuser)
        {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($comments);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Комментарий удален'
            );
            return $this->redirectToRoute('post_show', ['post_id' => $post_id]);
        }
        return $this->redirectToRoute('user_show_login');
    }
}