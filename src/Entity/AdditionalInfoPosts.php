<?php

namespace App\Entity;

use App\Repository\AdditionalInfoPostsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdditionalInfoPostsRepository::class)]
class AdditionalInfoPosts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'decimal', precision: 3, scale: 1)]
    private $rating;

    #[ORM\Column(type: 'integer')]
    private $countComments;

    #[ORM\Column(type: 'integer')]
    private $countRatings;

    #[ORM\Column(type: 'integer')]
    private $postId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getCountComments(): ?int
    {
        return $this->countComments;
    }

    public function setCountComments(int $countComments): self
    {
        $this->countComments = $countComments;

        return $this;
    }

    public function getCountRatings(): ?int
    {
        return $this->countRatings;
    }

    public function setCountRatings(int $countRatings): self
    {
        $this->countRatings = $countRatings;

        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }
}
