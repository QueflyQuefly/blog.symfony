<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;


class UserService
{
    private $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        ManagerRegistry $doctrine, 
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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
     * @return Users[] Returns an array of Users objects
     */
    public function getUsers(int $numberOfUsers, int $page)
    {
        $lessThanMaxId = $page * $numberOfUsers - $numberOfUsers;

        return $this->userRepository->getUsers($numberOfUsers, $lessThanMaxId);
    }

    public function delete($user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}