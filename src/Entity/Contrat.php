<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;  


#[ORM\Entity(repositoryClass: ContratRepository::class)]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $clauses = null;

    #[ORM\OneToOne(inversedBy: 'contrat', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Vous devez sélectionner une offre acceptée.")]
    private ?Offre $offre = null;

    #[ORM\Column]
    private ?bool $signeeOwnerAnnonce = false;

    #[ORM\Column]
    private ?bool $signeeOffreMaker = false;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClauses(): ?string
    {
        return $this->clauses;
    }

    public function setClauses(string $clauses): static
    {
        $this->clauses = $clauses;

        return $this;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(Offre $offre): static
    {
        $this->offre = $offre;

        return $this;
    }

    public function isSigneeOwnerAnnonce(): ?bool
    {
        return $this->signeeOwnerAnnonce;
    }

    public function setSigneeOwnerAnnonce(bool $signeeOwnerAnnonce): static
    {
        $this->signeeOwnerAnnonce = $signeeOwnerAnnonce;

        return $this;
    }

    public function isSigneeOffreMaker(): ?bool
    {
        return $this->signeeOffreMaker;
    }

    public function setSigneeOffreMaker(bool $signeeOffreMaker): static
    {
        $this->signeeOffreMaker = $signeeOffreMaker;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
