<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'comment:list']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'comment:item']]],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false,
)]
#[ApiFilter(SearchFilter::class, properties: ['conference' => 'exact'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['comment:list', 'comment:item'])]
    private $id;

    #[MaxDepth(2)]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'text')]
    #[Groups(['comment:list', 'comment:item'])]
    private $content;

    #[ORM\Column(type: 'integer')]
    #[Groups(['comment:list', 'comment:item'])]
    private $dateTime;

    #[ORM\Column(type: 'integer')]
    #[Groups(['comment:list', 'comment:item'])]
    private $rating;

    #[MaxDepth(1)]
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private $post;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: RatingComment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $ratingComments;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['comment:list', 'comment:item'])]
    private $approve = false;

    public function __construct()
    {
        $this->ratingComments = new ArrayCollection();
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

    public function getApprove(): ?bool
    {
        return $this->approve;
    }

    public function setApprove(bool $approve): self
    {
        $this->approve = $approve;

        return $this;
    }
}