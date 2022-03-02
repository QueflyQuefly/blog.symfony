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
    private ManagerRegistry $doctrine;
    private CommentsRepository $commentsRepository;
    private AdditionalInfoPostsRepository $additionalInfoPostsRepository;

    public function __construct(
        //Request $request, 
        ManagerRegistry $doctrine, 
        CommentsRepository $commentsRepository,
        AdditionalInfoPostsRepository $additionalInfoPostsRepository
    )
    {
        //$request = $request;
        $this->commentsRepository = $commentsRepository;
        $this->doctrine = $doctrine;
        $this->additionalInfoPostsRepository = $additionalInfoPostsRepository;
    }

    #[Route('/add/{postId}', name: 'add', methods: ['POST'], requirements: ['postId' => '\b[0-9]+'])]
    public function add(int $postId, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('user_login');
        }
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $content)
        {
            $entityManager = $this->doctrine->getManager();

            $comment = new Comments();
            $comment->setPostId($postId);
            $comment->setUserId(3); //fffffffffffffffffffffffffffffffffffffff
            $comment->setDateTime(time());
            $comment->setContent($content);
            $comment->setRating(0);
            $entityManager->persist($comment);
            $entityManager->flush();

            $infoPost = $this->additionalInfoPostsRepository->find($postId);
            $infoPost->setCountComments($infoPost->getCountComments() + 1);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Комментарий добавлен'
            );
        } else {
            $this->addFlash(
                'error',
                'При добавлении комментария произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    #[Route('/like/{postId}/{commentId}', name: 'like', methods: ['POST'], requirements: ['postId' => '\b[0-9]+', 'commentId' => '\b[0-9]+'])]
    public function like(int $postId, int $commentId): Response
    {
        if (!$this->isGranted('ROLE_USER'))
        {
            return $this->redirectToRoute('user_login');
        }
        $this->commentsRepository->like($commentId, $this->sessionUserId, $this->doctrine);
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }
    
    #[Route('/delete/{commentId}/{postId}', name: 'delete', methods: ['POST'], requirements: ['commentId' => '\b[0-9]+', 'postId' => '\b[0-9]+'])]
    public function delete(Comments $comments, int $postId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($comments);
        $entityManager->flush();

        $infoPost = $this->additionalInfoPostsRepository->find($postId);
        $infoPost->setCountComments($infoPost->getCountComments() - 1);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Комментарий удален'
        );
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }
}