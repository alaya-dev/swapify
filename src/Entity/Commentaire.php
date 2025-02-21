<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\HasLifecycleCallbacks] // Enable lifecycle callbacks
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ContenuCmt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null; // Automatically set on creation

    #[ORM\ManyToOne(inversedBy: 'listeCommentaires')]
    private ?Blog $blog = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuCmt(): ?string
    {
        return $this->ContenuCmt;
    }

    public function setContenuCmt(string $ContenuCmt): static
    {
        $this->ContenuCmt = $ContenuCmt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist] // Automatically set createdAt before persisting the entity
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable(); // Set the current date and time
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): static
    {
        $this->blog = $blog;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}