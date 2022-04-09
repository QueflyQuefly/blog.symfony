<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Subscription;
use App\Repository\UserRepository;
use App\Repository\SubscriptionRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private SubscriptionRepository $subscriptionRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @return User Returns an User object
     */
    public function register(
        string $email,
        string $fio,
        string $password,
        array $rights,
        $dateTime = false,
        bool $flush = true
    ) {
        if (!$dateTime){
            $dateTime = time();
        }
        $user = new User();
        $user->setEmail($email);
        $user->setFio($fio);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $password)
        );
        $user->setDateTime($dateTime);
        $user->setRoles($rights);
        $this->userRepository->add($user, $flush);
        
        return $user;
    }

    /**
     * @return User Returns an User object
     */
    public function getUserById(int $userId)
    {
        return $this->userRepository->find($userId);
    }

    /**
     * @return int Returns a max id of table user
     */
    public function getLastUserId()
    {
        return $this->userRepository->getLastUserId();
    }

    /**
     * @return bool Returns true if Subscription created
     */
    public function subscribe(User $userSubscribed, User $user, bool $flush = true)
    {
        if ($subscription = $this->isSubscribe($userSubscribed->getId(), $user->getId())) {
            $this->subscriptionRepository->remove($subscription, $flush);

            return false;
        } else {
            $subscription = new Subscription();
            $subscription->setUserSubscribed($userSubscribed);
            $subscription->setUser($user);
            $this->subscriptionRepository->add($subscription, $flush);

            return true;
        }
    }

    /**
     * @return Subscription|bool Returns an object of Subscription if user subscribed
     */
    public function isSubscribe(int $userIdWantSubscribe, int $userId)
    {
        if ($subscription = $this->subscriptionRepository->findOneBy([
            'userSubscribed' => $userIdWantSubscribe,
            'user'           => $userId
        ])) {
            return $subscription;
        }
        return false;
    }

    /**
     * @return [] Returns an array of emails, which subscribed on user
     */
    public function getSubscribedUsersEmails(User $user)
    {
        return $this->userRepository->getSubscribedUsersEmails($user->getId());
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

    /**
     * @return bool Returns true if User updated
     */
    public function update(User $user, bool $flush = true)
    {
        if ($user->getId()) {
            $this->userRepository->add($user, $flush);

            return true;
        }
        return false;
    }

    public function delete($user, bool $flush = true)
    {
        $this->userRepository->remove($user, $flush);
    }
}