<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;



#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $profile_picture = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

   // Champ plainPassword (non persistant)
    private ?string $plainPassword = null;


    #[ORM\Column(type: "text", nullable: true)] 
    private ?string $objectives = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column(type: "boolean")]
    private bool $certified; 

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(type: "string", nullable: true)] 
    private ?string $background_image = null; 
    
    #[ORM\Column(type: "text", nullable: true)] 
    private ?string $who_am_i = null; 
    
    #[ORM\Column(type: "text", nullable: true)] 
    private ?string $experience = null; 
    
    #[ORM\Column(type: "text", nullable: true)] 
    private ?string $qualities = null;

    

    public function __construct()
    {
        $this->certified = false;
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
{
    return $this->password;
}

public function setPassword(string $password): static
{
    $this->password = $password;

    return $this;
}

    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }

    public function setProfilePicture(string $profile_picture): static
    {
        $this->profile_picture = $profile_picture;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectives(): ?string
    {
        return $this->objectives;
    }

    public function setObjectives(string $objectives): static
    {
        $this->objectives = $objectives;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getCertified(): bool
    {

       return $this->certified;

    }

    public function getPlainPassword(): ?string
{
    return $this->plainPassword;
}

public function setPlainPassword(?string $plainPassword): self
{
    $this->plainPassword = $plainPassword;
    return $this;
}


public function eraseCredentials(): void
{
    // Supprime les informations sensibles après la connexion
    $this->plainPassword = null;
}

   public function setCertified(bool $certified): static
   {
    $this->certified = $certified;

    return $this;
   }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getBackgroundImage(): ?string 
    { 
        return $this->background_image; 
    } 
    
    public function setBackgroundImage(?string $background_image): self 
    { 
        $this->background_image = $background_image; return $this;
    } 
    
    public function getWhoAmI(): ?string 
    { 
        return $this->who_am_i; 
    } 
    
    public function setWhoAmI(?string $who_am_i): self 
    { 
        $this->who_am_i = $who_am_i; return $this; 
    } 
    
    public function getExperience(): ?string 
    { 
        return $this->experience; 
    } 
    
    public function setExperience(?string $experience): self 
    { 
        $this->experience = $experience; return $this; 
    } 
    
    public function getQualities(): ?string 
    { 
        return $this->qualities; 
    } 
    
    public function setQualities(?string $qualities): self 
    { 
        $this->qualities = $qualities; return $this; 

    }

    // Implémentation de l'interface PasswordAuthenticatedUserInterface
    public function getSalt(): ?string
    {
        // Si vous utilisez bcrypt ou argon2, vous n'avez pas besoin de salt.
        return null;
    }

    

    public function getRoles(): array
    {
        // Retourne les rôles de l'utilisateur (par exemple ['ROLE_USER'])
        return [$this->role];
    }

    public function getUserIdentifier(): string
    {
        // Symfony utilise cette méthode pour identifier l'utilisateur (utilisez le champ "username" ou "email")
        return $this->email;
    }

    
}
