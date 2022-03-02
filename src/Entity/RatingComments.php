<?php

namespace App\Entity;

use App\Repository\RatingCommentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingCommentsRepository::class)]
class RatingComments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $commentId;

    #[ORM\Column(type: 'integer')]
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentId(): ?int
    {
        return $this->commentId;
    }

    public function setCommentId(int $commentId): self
    {
        $this->commentId = $commentId;

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
