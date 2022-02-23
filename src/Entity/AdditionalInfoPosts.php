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
    private $count_comments;

    #[ORM\Column(type: 'integer')]
    private $count_ratings;

    #[ORM\Column(type: 'integer')]
    private $post_id;

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
        return $this->count_comments;
    }

    public function setCountComments(int $count_comments): self
    {
        $this->count_comments = $count_comments;

        return $this;
    }

    public function getCountRatings(): ?int
    {
        return $this->count_ratings;
    }

    public function setCountRatings(int $count_ratings): self
    {
        $this->count_ratings = $count_ratings;

        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): self
    {
        $this->post_id = $post_id;

        return $this;
    }
}
