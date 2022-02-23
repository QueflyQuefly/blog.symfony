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
    private $user_id_want_subscribe;

    #[ORM\Column(type: 'integer')]
    private $user_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdWantSubscribe(): ?int
    {
        return $this->user_id_want_subscribe;
    }

    public function setUserIdWantSubscribe(int $user_id_want_subscribe): self
    {
        $this->user_id_want_subscribe = $user_id_want_subscribe;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }
}
