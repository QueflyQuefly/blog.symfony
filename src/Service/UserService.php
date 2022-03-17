<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Subscription;
use App\Repository\UserRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
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
    public function subscribe(User $userSubscribed, User $user)
    {
        if ($subscription = $this->isSubscribe($userSubscribed->getId(), $user->getId())) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
            return false;
        } else {
            $subscription = new Subscription();
            $subscription->setUserSubscribed($userSubscribed);
            $subscription->setUser($user);
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
        if ($subscription = $this->subscriptionRepository->findOneBy([
            'userSubscribed' => $userIdWantSubscribe,
            'user' => $userId
        ])) {
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
        $users = [];
        if (strpos($searchWords, '@') !== false) {
            if ($result = $this->userRepository->findOneByEmail($searchWords)) {
                $users[] = $result;
            }
        }
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