<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "le nome de produit ne peux pas etre vide ")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "le description de produit ne peux pas etre vide ")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "le price de produit ne peux pas etre vide ")]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d+)?$/",
        message: "Le prix doit être un nombre (entier ou décimal)."
    )]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "le discoutPrice de produit ne peux pas etre vide ")]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d+)?$/",
        message: "Le discoutPrice doit être un nombre (entier ou décimal)."
    )]
    private ?float $discoutPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Assert\NotBlank(message: "veuiller selection ou le produit va etre publier")]
    private ?Souk $souk = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDiscoutPrice(): ?float
    {
        return $this->discoutPrice;
    }

    public function setDiscoutPrice(float $discoutPrice): static
    {
        $this->discoutPrice = $discoutPrice;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getSouk(): ?Souk
    {
        return $this->souk;
    }

    public function setSouk(?Souk $souk): static
    {
        $this->souk = $souk;

        return $this;
    }
}
