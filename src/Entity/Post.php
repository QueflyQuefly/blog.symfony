<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Cache(usage: "READ_WRITE")]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[MaxDepth(2)]
    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'text')]
    private $title;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'integer')]
    private $dateTime;

    #[ORM\Column(type: 'decimal', precision: 2, scale: 1)]
    private $rating;

    #[Ignore]
    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $comments;

    #[Ignore]
    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: RatingPost::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $ratingPosts;

    private $countRatingPosts;
    private $countComments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->ratingPosts = new ArrayCollection();
        $this->countRatingPosts = $this->getCountRatingPosts();
        $this->countComments = $this->getCountComments();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RatingPost>
     */
    public function getRatingPosts(): Collection
    {
        return $this->ratingPosts;
    }

    public function addRatingPost(RatingPost $ratingPost): self
    {
        if (!$this->ratingPosts->contains($ratingPost)) {
            $this->ratingPosts[] = $ratingPost;
            $ratingPost->setPost($this);
        }

        return $this;
    }

    public function removeRatingPost(RatingPost $ratingPost): self
    {
        if ($this->ratingPosts->removeElement($ratingPost)) {
            // set the owning side to null (unless already changed)
            if ($ratingPost->getPost() === $this) {
                $ratingPost->setPost(null);
            }
        }

        return $this;
    }

    public function getCountComments(): int
    {
        if (is_null($this->countComments)) {
            $this->countComments = $this->comments->count();
        }

        return $this->countComments;
    }

    public function setCountComments(int $countComments): self
    {
        $this->countComments = $countComments;

        return $this;
    }

    public function getCountRatingPosts(): int
    {
        if (is_null($this->countRatingPosts)) {
            $this->countRatingPosts = $this->comments->count();
        }

        return $this->countRatingPosts;
    }

    public function setCountRatingPosts(int $countRatingPosts): self
    {
        $this->countRatingPosts = $countRatingPosts;

        return $this;
    }
}
