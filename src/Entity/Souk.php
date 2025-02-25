<?php

namespace App\Entity;

use App\Repository\SoukRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SoukRepository::class)]
class Souk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "le nom ne peux pas etre vide ")]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Veuillez entrer une date valide.")]
    #[Assert\GreaterThanOrEqual('today', message: "ola")]
    private ?\DateTimeInterface $startSouke = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Veuillez entrer une date valide.")]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "startSouke",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    private ?\DateTimeInterface $endSouke = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'souks')]
    private Collection $participant;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(mappedBy: 'souk', targetEntity: Product::class,cascade:['remove'])]
    private Collection $products;

    public function __construct()
    {
        $this->participant = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->startSouke = new \DateTime();
        $this->endSouke = new \DateTime();
    }

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

    public function getStartSouke(): ?\DateTimeInterface
    {
        return $this->startSouke;
    }

    public function setStartSouke(\DateTimeInterface $startSouke): static
    {
        $this->startSouke = $startSouke;

        return $this;
    }

    public function getEndSouke(): ?\DateTimeInterface
    {
        return $this->endSouke;
    }

    public function setEndSouke(\DateTimeInterface $endSouke): static
    {
        $this->endSouke = $endSouke;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participant->removeElement($participant);

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setSouk($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSouk() === $this) {
                $product->setSouk(null);
            }
        }

        return $this;
    }
}
