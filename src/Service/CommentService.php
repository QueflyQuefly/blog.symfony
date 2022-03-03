<?php

namespace App\Service;

use App\Entity\RatingComments;
use App\Repository\RatingCommentsRepository;
use App\Repository\CommentsRepository;
use Doctrine\Persistence\ManagerRegistry;


class CommentService
{
    private ManagerRegistry $doctrine;
    private CommentsRepository $commentsRepository;
    private RatingCommentsRepository $ratingCommentsRepository;

    public function __construct(
        ManagerRegistry $doctrine, 
        CommentsRepository $commentsRepository,
        RatingCommentsRepository $ratingCommentsRepository
    )
    {
        $this->commentsRepository = $commentsRepository;
        $this->doctrine = $doctrine;
        $this->ratingCommentsRepository = $ratingCommentsRepository;
    }

    /**
     * @return bool
     */
    public function like(int $commentId, int $userId)
    {
        $ratingComment = $this->ratingCommentsRepository->findOneBy(['userId' => $userId, 'commentId' => $commentId]);
        $comment = $this->commentsRepository->find($commentId);

        $entityManager = $this->doctrine->getManager();

        if ($ratingComment)
        {
            $entityManager->remove($ratingComment);
            $entityManager->flush();

            $comment->setRating($comment->getRating() - 1);
            $entityManager->flush();
        } else {
            $ratingComment = new RatingComments();
            $ratingComment->setUserId($userId);
            $ratingComment->setCommentId($commentId);
            $entityManager->persist($ratingComment);
            $entityManager->flush();

            $comment->setRating($comment->getRating() + 1);
            $entityManager->flush();
        }
        return true;
    }
}