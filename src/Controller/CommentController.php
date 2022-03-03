<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Service\CommentService;
use App\Repository\AdditionalInfoPostsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private CommentService $commentService;
    private AdditionalInfoPostsRepository $additionalInfoPostsRepository;

    public function __construct(
        ManagerRegistry $doctrine, 
        CommentService $commentService,
        AdditionalInfoPostsRepository $additionalInfoPostsRepository
    )
    {
        $this->commentService = $commentService;
        $this->doctrine = $doctrine;
        $this->additionalInfoPostsRepository = $additionalInfoPostsRepository;
    }

    #[Route('/add/{postId}', name: 'add', methods: ['POST'], requirements: ['postId' => '\b[0-9]+'])]
    public function add(int $postId, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sessionUserId = $this->getUserId();
        $content = $request->request->get('content');
        $content = trim(strip_tags($content));
        if ('' != $content)
        {
            $entityManager = $this->doctrine->getManager();

            $comment = new Comments();
            $comment->setPostId($postId);
            $comment->setUserId($sessionUserId);
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
                'Произошла ошибка: заполните поля формы'
            );
        }
        return $this->redirectToRoute('post_show', ['postId' => $postId]);
    }

    #[Route('/like/{postId}/{commentId}', name: 'like', methods: ['POST'], requirements: ['postId' => '\b[0-9]+', 'commentId' => '\b[0-9]+'])]
    public function like(int $postId, int $commentId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sessionUserId = $this->getUserId();
        $this->commentService->like($commentId, $sessionUserId);
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