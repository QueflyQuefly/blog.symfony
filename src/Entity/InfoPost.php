<?php

namespace App\Entity;

use App\Repository\InfoPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfoPostRepository::class)]
class InfoPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(inversedBy: 'infoPost', targetEntity: Post::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $post;

    #[ORM\Column(type: 'integer')]
    private $countComments;

    #[ORM\Column(type: 'integer')]
    private $countRatings;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(Post $post): self
    {
        $this->post = $post;

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
}
