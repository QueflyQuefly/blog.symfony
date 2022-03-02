<?php

namespace App\Entity;

use App\Repository\SubscriptionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionsRepository::class)]
class Subscriptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $userIdWantSubscribe;

    #[ORM\Column(type: 'integer')]
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdWantSubscribe(): ?int
    {
        return $this->userIdWantSubscribe;
    }

    public function setUserIdWantSubscribe(int $userIdWantSubscribe): self
    {
        $this->userIdWantSubscribe = $userIdWantSubscribe;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
