<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    private ?User $idRecepteur = null;

    #[ORM\ManyToOne]
    private ?User $idDonneur = null;

    #[ORM\Column]
    private ?int $rating = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdRecepteur(): ?User
    {
        return $this->idRecepteur;
    }

    public function setIdRecepteur(?User $idRecepteur): static
    {
        $this->idRecepteur = $idRecepteur;

        return $this;
    }

    public function getIdDonneur(): ?User
    {
        return $this->idDonneur;
    }

    public function setIdDonneur(?User $idDonneur): static
    {
        $this->idDonneur = $idDonneur;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}
