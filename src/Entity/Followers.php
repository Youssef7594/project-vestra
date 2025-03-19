<?php

namespace App\Entity;

use App\Repository\FollowersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowersRepository::class)]
class Followers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $follower = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $followed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?Users
    {
        return $this->follower;
    }

    public function setFollower(Users $follower): static
    {
        $this->follower = $follower;
        return $this;
    }

    public function getFollowed(): ?Users
    {
        return $this->followed;
    }

    public function setFollowed(Users $followed): static
    {
        $this->followed = $followed;
        return $this;
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
}
