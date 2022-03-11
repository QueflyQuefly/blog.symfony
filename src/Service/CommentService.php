<?php

namespace App\Service;

use App\Entity\Comments;
use App\Entity\RatingComments;
use App\Repository\RatingCommentsRepository;
use App\Repository\AdditionalInfoPostsRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;


class CommentService
{
    private EntityManagerInterface $entityManager;
    private CommentsRepository $commentsRepository;
    private RatingCommentsRepository $ratingCommentsRepository;
    private AdditionalInfoPostsRepository $additionalInfoPostsRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        CommentsRepository $commentsRepository,
        RatingCommentsRepository $ratingCommentsRepository,
        AdditionalInfoPostsRepository $additionalInfoPostsRepository
    )
    {
        $this->commentsRepository = $commentsRepository;
        $this->entityManager = $entityManager;
        $this->ratingCommentsRepository = $ratingCommentsRepository;
        $this->additionalInfoPostsRepository = $additionalInfoPostsRepository;
    }

    /**
     * @return Comments Returns an object of Comments
     */
    public function create(int $userId, int $postId, string $content, int $rating = 0, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $comment = new Comments();
        $comment->setPostId($postId);
        $comment->setUserId($userId);
        $comment->setDateTime($dateTime);
        $comment->setContent($content);
        $comment->setRating($rating);
        $this->entityManager->persist($comment);
        $infoPost = $this->additionalInfoPostsRepository->find($postId);
        $infoPost->setCountComments($infoPost->getCountComments() + 1);
        $this->entityManager->flush();
        return $comment;
    }

    /**
     * @return Comments Returns an object of Comments
     */
    public function createWithoutFlush(int $userId, int $postId, string $content, int $rating = 0, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $comment = new Comments();
        $comment->setPostId($postId);
        $comment->setUserId($userId);
        $comment->setDateTime($dateTime);
        $comment->setContent($content);
        $comment->setRating($rating);
        $this->entityManager->persist($comment);
        $infoPost = $this->additionalInfoPostsRepository->find($postId);
        $infoPost->setCountComments($infoPost->getCountComments() + 1);
        return $comment;
    }

    public function like(int $userId, int $commentId)
    {
        $ratingComment = $this->ratingCommentsRepository->findOneBy(['userId' => $userId, 'commentId' => $commentId]);
        $comment = $this->commentsRepository->find($commentId);

        if ($ratingComment)
        {
            $this->entityManager->remove($ratingComment);
            $comment->setRating($comment->getRating() - 1);
            $this->entityManager->flush();
        } else {
            $ratingComment = new RatingComments();
            $ratingComment->setUserId($userId);
            $ratingComment->setCommentId($commentId);
            $this->entityManager->persist($ratingComment);
            $comment->setRating($comment->getRating() + 1);
            $this->entityManager->flush();
        }
    }

    public function likeWithoutFlush(int $userId, int $commentId)
    {
        $ratingComment = $this->ratingCommentsRepository->findOneBy(['userId' => $userId, 'commentId' => $commentId]);
        $comment = $this->commentsRepository->find($commentId);

        if ($ratingComment)
        {
            $this->entityManager->remove($ratingComment);
            $comment->setRating($comment->getRating() - 1);
        } else {
            $ratingComment = new RatingComments();
            $ratingComment->setUserId($userId);
            $ratingComment->setCommentId($commentId);
            $this->entityManager->persist($ratingComment);
            $comment->setRating($comment->getRating() + 1);
        }
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function getComments(int $numberOfComments, int $page)
    {
        $lessThanMaxId = $page * $numberOfComments - $numberOfComments;

        return $this->commentsRepository->getComments($numberOfComments, $lessThanMaxId);
    }
    
    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function getCommentsByPostId(int $postId)
    {
        return $this->commentsRepository->getCommentsByPostId($postId);
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function getCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this->commentsRepository->getCommentsByUserId($userId, $numberOfComments);
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function getLikedCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this->commentsRepository->getLikedCommentsByUserId($userId, $numberOfComments);
    }

    public function delete($comment, $postId)
    {
        $this->entityManager->remove($comment);
        $infoPost = $this->additionalInfoPostsRepository->find($postId);
        $infoPost->setCountComments($infoPost->getCountComments() - 1);
        $this->entityManager->flush();
    }
}