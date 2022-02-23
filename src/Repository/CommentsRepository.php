<?php

namespace App\Repository;

use App\Entity\Comments;
use App\Entity\RatingComments;
use App\Repository\RatingCommentsRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comments[]    findAll()
 * @method Comments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function findByUserId(int $userId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user_id = :val')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Comments[] Returns an array of Comments objects
     */
    public function findByPostId(int $postId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.post_id = :val')
            ->setParameter('val', $postId)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return bool
     */
    public function like(int $commentId, int $userId, ManagerRegistry $doctrine)
    {
        $ratingRepository = new RatingCommentsRepository($doctrine);

        $ratingComment = $ratingRepository->findOneBy(['user_id' => $userId, 'comment_id' => $commentId]);
        $entityManager = $doctrine->getManager();
        $comment = $entityManager->getRepository(Comments::class)->find($commentId);

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

    // /**
    //  * @return Comments[] Returns an array of Comments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comments
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
