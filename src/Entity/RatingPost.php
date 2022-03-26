<?php

namespace App\Entity;

use App\Repository\RatingPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingPostRepository::class)]
#[ORM\Cache(usage:"READ_ONLY")]
class RatingPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'ratingPosts', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private $post;

    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ratingPosts', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'decimal', precision: 2, scale: '0')]
    private $rating;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
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
}
