<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $email;

    #[ORM\Column(type: 'string', length: 50)]
    private $fio;

    #[ORM\Column(type: 'string', length: 60)]
    private $pass_word;

    #[ORM\Column(type: 'smallint')]
    private $date_time;

    #[ORM\Column(type: 'string', length: 20)]
    private $rights;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFio(): ?string
    {
        return $this->fio;
    }

    public function setFio(string $fio): self
    {
        $this->fio = $fio;

        return $this;
    }

    public function getPassWord(): ?string
    {
        return $this->pass_word;
    }

    public function setPassWord(string $pass_word): self
    {
        $this->pass_word = $pass_word;

        return $this;
    }

    public function getDateTime(): ?int
    {
        return $this->date_time;
    }

    public function setDateTime(int $date_time): self
    {
        $this->date_time = $date_time;

        return $this;
    }

    public function getRights(): ?string
    {
        return $this->rights;
    }

    public function setRights(string $rights): self
    {
        $this->rights = $rights;

        return $this;
    }
}
