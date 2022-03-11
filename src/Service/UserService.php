<?php

namespace App\Service;

use App\Entity\Subscriptions;
use App\Repository\UserRepository;
use App\Repository\SubscriptionsRepository;
use Doctrine\ORM\EntityManagerInterface;


class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SubscriptionsRepository $subscriptionsRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserRepository $userRepository,
        SubscriptionsRepository $subscriptionsRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return User Returns an User object
     */
    public function getUserById(int $userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * @return int - Returns an id of User object
     */
    public function getLastUserId()
    {
        return $this->userRepository->getLastUserId();
    }

    /**
     * @return bool
     */
    public function subscribe(int $userIdWantSubscribe, int $userId)
    {
        if ($subscription = $this->isSubscribe($userIdWantSubscribe, $userId))
        {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
            return false;
        } else {
            $subscription = new Subscriptions();
            $subscription->setUserIdWantSubscribe($userIdWantSubscribe);
            $subscription->setUserId($userId);
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
            return true;
        }
    }

    /**
     * @return Subcriptions|bool
     */
    public function isSubscribe(int $userIdWantSubscribe, int $userId)
    {
        if ($subscription = $this->subscriptionsRepository->findOneBy([
            'userIdWantSubscribe' => $userIdWantSubscribe,
            'userId' => $userId
        ]))
        {
            return $subscription;
        }
        return false;
    }

    /**
     * @return User[] Returns an array of User objects
     */
    public function getUsers(int $numberOfUsers, int $page)
    {
        $lessThanMaxId = $page * $numberOfUsers - $numberOfUsers;

        return $this->userRepository->getUsers($numberOfUsers, $lessThanMaxId);
    }

    /**
     * @return User[] Returns an array of Users objects
     */
    public function searchUsers(string $searchWords)
    {
        $users = $this->userRepository->findOneByEmail($searchWords);
        $users1 = $this->userRepository->searchByFio('%'.$searchWords.'%');
        $results = array_merge($users, $users1);
        return $results;
    }

    public function delete($user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}