<?php

namespace App\Service;

use App\Entity\Subscriptions;
use App\Repository\UserRepository;
use App\Repository\SubscriptionsRepository;
use Doctrine\Persistence\ManagerRegistry;


class UserService
{
    private $entityManager;
    private UserRepository $userRepository;
    private SubscriptionsRepository $subscriptionsRepository;

    public function __construct(
        ManagerRegistry $doctrine, 
        UserRepository $userRepository,
        SubscriptionsRepository $subscriptionsRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * @return User Returns an User object
     */
    public function find(int $userId)
    {
        return $this->userRepository->find($userId);
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
     * @return Users[] Returns an array of Users objects
     */
    public function getUsers(int $numberOfUsers, int $page)
    {
        $lessThanMaxId = $page * $numberOfUsers - $numberOfUsers;

        return $this->userRepository->getUsers($numberOfUsers, $lessThanMaxId);
    }

    /**
     * @return Users[] Returns an array of Users objects
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