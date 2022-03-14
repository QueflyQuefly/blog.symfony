<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\RatingComment;
use App\Repository\RatingCommentRepository;
use App\Repository\InfoPostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;


class CommentService
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private RatingCommentRepository $ratingCommentRepository;
    private InfoPostRepository $infoPostRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        CommentRepository $commentRepository,
        RatingCommentRepository $ratingCommentRepository,
        InfoPostRepository $infoPostRepository
    )
    {
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
        $this->ratingCommentRepository = $ratingCommentRepository;
        $this->infoPostRepository = $infoPostRepository;
    }

    /**
     * @return Comment Returns an object of Comment
     */
    public function create(User $user, Post $post, string $content, int $rating = 0, $dateTime = false)
    {
        if (!$dateTime)
        {
            $dateTime = time();
        }
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setDateTime($dateTime);
        $comment->setContent($content);
        $comment->setRating($rating);
        $this->entityManager->persist($comment);
        $infoPost = $post->getInfoPost();
        $infoPost->setCountComments($infoPost->getCountComments() + 1);
        $this->entityManager->flush();
        return $comment;
    }

    public function like(User $user, Comment $comment)
    {
        $ratingComment = $this->ratingCommentRepository->findOneBy([
            'user' => $user, 
            'comment' => $comment
        ]);
        $comment = $this->commentRepository->find($comment);

        if ($ratingComment)
        {
            $this->entityManager->remove($ratingComment);
            $comment->setRating($comment->getRating() - 1);
        } else {
            $ratingComment = new RatingComment();
            $ratingComment->setUser($user);
            $ratingComment->setComment($comment);
            $this->entityManager->persist($ratingComment);
            $comment->setRating($comment->getRating() + 1);
        }
        $this->entityManager->flush();
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getComments(int $numberOfComments, int $page)
    {
        $lessThanMaxId = $page * $numberOfComments - $numberOfComments;

        return $this->commentRepository->getComments($numberOfComments, $lessThanMaxId);
    }
    
    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByPostId(int $postId)
    {
        return $this->commentRepository->getCommentsByPostId($postId);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this->commentRepository->getCommentsByUserId($userId, $numberOfComments);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getLikedCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this->commentRepository->getLikedCommentsByUserId($userId, $numberOfComments);
    }

    public function delete($comment, $postId)
    {
        $this->entityManager->remove($comment);
        $infoPost = $this->infoPostRepository->find($postId);
        $infoPost->setCountComments($infoPost->getCountComments() - 1);
        $this->entityManager->flush();
    }
}