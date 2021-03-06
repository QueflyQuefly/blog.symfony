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
        CommentRepository       $commentRepository,
        RatingCommentRepository $ratingCommentRepository
    ) {
        $this->commentRepository       = $commentRepository;
        $this->ratingCommentRepository = $ratingCommentRepository;
    }

    /**
     * @return Comment Returns an object of Comment
     */
    public function create(
        User   $user,
        Post   $post,
        string $content,
        bool   $approve  = false,
        int    $rating   = 0,
        ?int   $dateTime = null,
        bool   $flush    = true
    ) {
        if (empty($dateTime)) {
            $dateTime = time();
        }

        $comment = (new Comment())
            ->setPost($post)
            ->setUser($user)
            ->setDateTime($dateTime)
            ->setContent($content)
            ->setRating($rating)
            ->setApprove($approve);
        $this
            ->commentRepository
            ->add($comment, $flush);
        
        return $comment;
    }

    public function approve(Comment $comment, bool $flush = true)
    {
        $this
            ->commentRepository
            ->approve($comment, $flush);
    }

    /**
     * @return bool Returns true if like added
     */
    public function changeLike(User $user, Comment $comment, bool $flush = true)
    {
        $ratingComment = $this->ratingCommentRepository->findOneBy([
            'user'    => $user, 
            'comment' => $comment,
        ]);

        if (! empty($ratingComment)) {
            $this->removeLike($ratingComment, $comment, $flush);

            return false;
        }

        $this->addLike($user, $comment, $flush);

        return true;
    }

    /**
     * @return bool Returns true if like added
     */
    public function addLike(User $user, Comment $comment, bool $flush = true)
    {
        $ratingComment = (new RatingComment())
            ->setUser($user)
            ->setComment($comment);
        $comment->setRating($comment->getRating() + 1);
        $this
            ->ratingCommentRepository
            ->add($ratingComment, $flush);

        return true;
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getComments(int $numberOfComments, int $page)
    {
        $lessThanMaxId = $page * $numberOfComments - $numberOfComments;

        return $this
            ->commentRepository
            ->getComments($numberOfComments, $lessThanMaxId);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getNotApprovedComments(int $numberOfComments, int $page)
    {
        $lessThanMaxId = $page * $numberOfComments - $numberOfComments;

        return $this
            ->commentRepository
            ->getNotApprovedComments($numberOfComments, $lessThanMaxId);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByPostId(int $postId)
    {
        return $this
            ->commentRepository
            ->getCommentsByPostId($postId);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this
            ->commentRepository
            ->getCommentsByUserId($userId, $numberOfComments);
    }

    /**
     * @return Comment[] Returns an array of Comment objects
     */
    public function getLikedCommentsByUserId(int $userId, int $numberOfComments)
    {
        return $this
            ->commentRepository
            ->getLikedCommentsByUserId($userId, $numberOfComments);
    }

    /**
     * @return bool Returns true if Post updated
     */
    public function update(bool $flush = true)
    {
        if ($flush) {
            $this
                ->commentRepository
                ->update($flush);

            return true;
        }

        return false;
    }

    public function delete(Comment $comment, bool $flush = true)
    {
        $this
            ->commentRepository
            ->remove($comment, $flush);
    }

    /**
     * @return bool Returns true if like added
     */
    private function removeLike(RatingComment $ratingComment, Comment $comment, bool $flush = true)
    {
        $comment->setRating($comment->getRating() - 1);
        $this
            ->ratingCommentRepository
            ->remove($ratingComment, $flush);

        return true;
    }
}