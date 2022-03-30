<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\RatingComment;
use App\Repository\RatingCommentRepository;
use App\Repository\CommentRepository;

class CommentService
{
    private CommentRepository $commentRepository;
    private RatingCommentRepository $ratingCommentRepository;

    public function __construct(
        CommentRepository $commentRepository,
        RatingCommentRepository $ratingCommentRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->ratingCommentRepository = $ratingCommentRepository;
    }

    /**
     * @return Comment Returns an object of Comment
     */
    public function create(User $user, Post $post, string $content, int $rating = 0, $dateTime = false, bool $flush = true)
    {
        if (!$dateTime) {
            $dateTime = time();
        }
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($user);
        $comment->setDateTime($dateTime);
        $comment->setContent($content);
        $comment->setRating($rating);
        $this->commentRepository->add($comment, $flush);
        
        return $comment;
    }

    public function like(User $user, Comment $comment, $checkingForUser = true, bool $flush = true)
    {
        if ($checkingForUser) {
            $ratingComment = $this->ratingCommentRepository->findOneBy([
                'user' => $user, 
                'comment' => $comment
            ]);
            if ($ratingComment) {
                $comment->setRating($comment->getRating() - 1);
                $this->ratingCommentRepository->remove($ratingComment, $flush);

                return false;
            }
        }
        $ratingComment = new RatingComment();
        $ratingComment->setUser($user);
        $ratingComment->setComment($comment);
        $comment->setRating($comment->getRating() + 1);
        $this->ratingCommentRepository->add($ratingComment, $flush);

        return true;
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

    public function delete(Comment $comment, bool $flush = true)
    {
        $this->commentRepository->remove($comment, $flush);
    }
}