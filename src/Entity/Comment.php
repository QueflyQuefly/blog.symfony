<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'integer')]
    private $dateTime;

    #[ORM\Column(type: 'integer')]
    private $rating;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private $post;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: RatingComment::class, orphanRemoval: true)]
    private $ratingComments;

    public function __construct()
    {
        $this->ratingComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDateTime(): ?int
    {
        return $this->dateTime;
    }

    public function setDateTime(int $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
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

    /**
     * @return Collection<int, RatingComment>
     */
    public function getRatingComments(): Collection
    {
        return $this->ratingComments;
    }

    public function addRatingComment(RatingComment $ratingComment): self
    {
        if (!$this->ratingComments->contains($ratingComment)) {
            $this->ratingComments[] = $ratingComment;
            $ratingComment->setComment($this);
        }

        return $this;
    }

    public function removeRatingComment(RatingComment $ratingComment): self
    {
        if ($this->ratingComments->removeElement($ratingComment)) {
            // set the owning side to null (unless already changed)
            if ($ratingComment->getComment() === $this) {
                $ratingComment->setComment(null);
            }
        }

        return $this;
    }
}
