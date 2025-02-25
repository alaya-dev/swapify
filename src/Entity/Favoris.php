<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favoris',targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'favoris',targetEntity: Annonce::class)]
    private ?Annonce $annonces = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAnnonces(): ?Annonce
    {
        return $this->annonces;
    }

    public function setAnnonces(?Annonce $annonces): static
    {
        $this->annonces = $annonces;

        return $this;
    }
}
