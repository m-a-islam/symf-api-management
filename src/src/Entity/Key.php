<?php

namespace App\Entity;

use App\Repository\KeyRepository;
use Doctrine\ORM\Mapping as ORM;
// use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: KeyRepository::class)]
#[ORM\Table(name: '`keys`')] // It's good practice to name tables in plural
#[ORM\HasLifecycleCallbacks] // Enable automatic timestamping
class Key
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['key:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['key:read', 'key:write'])]
    private ?string $KeyIdentifier = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['active', 'inactive'])]
    #[Groups(['key:read', 'key:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['key:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['key:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        // Set default values when a new Key object is created
        $this->status = 'active';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyIdentifier(): ?string
    {
        return $this->KeyIdentifier;
    }

    public function setKeyIdentifier(string $KeyIdentifier): static
    {
        $this->KeyIdentifier = $KeyIdentifier;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist] // <<< CHECK THIS LINE. This links the method to the "save" event.
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
