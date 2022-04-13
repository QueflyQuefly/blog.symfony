<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;

#[UniqueEntity(fields: ["email"], message: "Уже есть аккаунт с таким email. Войдите")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\Email(message: "'{{ value }}' не является настоящим адресом email")]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[Ignore]
    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'integer')]
    private $dateTime;

    #[ORM\Column(type: 'string', length: 50)]
    private $fio;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $posts;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $comments;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RatingPost::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $ratingPosts;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RatingComment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $ratingComments;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'userSubscribed', targetEntity: Subscription::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $subscriptions;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Subscription::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private $mySubscriptions;

    #[ORM\Column(type: 'integer')]
    private $isBanned;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ratingPosts = new ArrayCollection();
        $this->ratingComments = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->mySubscriptions = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFio(): ?string
    {
        return $this->fio;
    }

    public function setFio(string $fio): self
    {
        $this->fio = $fio;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
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
            $ratingPost->setUser($this);
        }

        return $this;
    }

    public function removeRatingPost(RatingPost $ratingPost): self
    {
        if ($this->ratingPosts->removeElement($ratingPost)) {
            // set the owning side to null (unless already changed)
            if ($ratingPost->getUser() === $this) {
                $ratingPost->setUser(null);
            }
        }

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
            $ratingComment->setUser($this);
        }

        return $this;
    }

    public function removeRatingComment(RatingComment $ratingComment): self
    {
        if ($this->ratingComments->removeElement($ratingComment)) {
            // set the owning side to null (unless already changed)
            if ($ratingComment->getUser() === $this) {
                $ratingComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setUserSubscribed($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getUserSubscribed() === $this) {
                $subscription->setUserSubscribed(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getMySubscriptions(): Collection
    {
        return $this->mySubscriptions;
    }

    public function addMySubscription(Subscription $mySubscription): self
    {
        if (!$this->mySubscriptions->contains($mySubscription)) {
            $this->mySubscriptions[] = $mySubscription;
            $mySubscription->setUser($this);
        }

        return $this;
    }

    public function removeMySubscription(Subscription $mySubscription): self
    {
        if ($this->mySubscriptions->removeElement($mySubscription)) {
            // set the owning side to null (unless already changed)
            if ($mySubscription->getUser() === $this) {
                $mySubscription->setUser(null);
            }
        }

        return $this;
    }

    public function getIsBanned(): ?int
    {
        return $this->isBanned;
    }

    public function setIsBanned(int $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }
}
