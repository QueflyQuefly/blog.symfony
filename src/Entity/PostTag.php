<?php

namespace App\Entity;

use App\Repository\PostTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostTagRepository::class)]
#[ORM\Cache(usage:"READ_ONLY")]
class PostTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Cache(usage:"READ_ONLY")]
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'postTags', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private $post;

    #[ORM\Column(type: 'string', length: 255)]
    private $tag;

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

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}
