<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @return Users[] Returns an array of Users objects
     */
    public function getUsers(int $numberOfUsers, int $lessThanMaxId)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult($lessThanMaxId)
            ->setMaxResults($numberOfUsers)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return int Returns an id
     */
    public function getLastUserId()
    {
        return $this->createQueryBuilder('u')
            ->select('MAX(u.id) as max_id')
            ->getQuery()
            ->getOneOrNullResult()['max_id']
        ;
    }

    /**
     * @return Users[] Returns an array of Users objects
     */
    public function searchByFio(string $search)
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->andWhere($qb->expr()->like('u.fio', ':search'))
            ->orderBy('u.id', 'DESC')
            ->setParameter('search', $search)
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
    }
}
