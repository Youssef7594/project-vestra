<?php

namespace App\Entity;

use App\Repository\NotificationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
class Notifications
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;  // ✅ Remplace `user_id` par un objet `Users`

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null; 

    #[ORM\Column]
    private ?bool $seen = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    /* public function getId(): ?int
    {
        return $this->id;
    } */

    public function getId(): ?int 
    {
        return $this->id; 
    }

    public function getUser(): ?Users 
    { 
        return $this->user; 
    }

    public function setUser(Users $user): self 
    { 
        $this->user = $user; return $this; 
    }

    public function getType(): ?string 
    { 
        return $this->type; 
    }

    public function setType(string $type): self 
    { 
        $this->type = $type; return $this; 
    }

    public function getMessage(): ?string 
    { 
        return $this->message; 
    }

    public function setMessage(string $message): self 
    { 
        $this->message = $message; return $this; 
    }

    public function isSeen(): ?bool 
    { 
        return $this->seen; 
    }

    public function setSeen(bool $seen): self 
    { 
        $this->seen = $seen; return $this; 
    }

    public function getCreatedAt(): ?\DateTimeInterface 
    { 
        return $this->created_at; 
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    } 

    /* public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): static
    {
        $this->seen = $seen;

        return $this;
    }*/



}
