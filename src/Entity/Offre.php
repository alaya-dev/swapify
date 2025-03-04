<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "veuiller entre une description pour votre offre ")]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonceName = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $annonceOwner = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $offerMaker = null;

    #[ORM\OneToOne(mappedBy: 'offre', cascade: ['persist', 'remove'])]
    private ?Contrat $contrat = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAnnonceName(): ?Annonce
    {
        return $this->annonceName;
    }

    public function setAnnonceName(?Annonce $annonceName): static
    {
        $this->annonceName = $annonceName;

        return $this;
    }

    public function getAnnonceOwner(): ?User
    {
        return $this->annonceOwner;
    }

    public function setAnnonceOwner(?User $annonceOwner): static
    {
        $this->annonceOwner = $annonceOwner;

        return $this;
    }

    public function getOfferMaker(): ?user
    {
        return $this->offerMaker;
    }

    public function setOfferMaker(?user $offerMaker): static
    {
        $this->offerMaker = $offerMaker;

        return $this;
    }

    public function getContrat(): ?Contrat
    {
        return $this->contrat;
    }

    public function setContrat(Contrat $contrat): static
    {
        // set the owning side of the relation if necessary
        if ($contrat->getOffre() !== $this) {
            $contrat->setOffre($this);
        }

        $this->contrat = $contrat;

        return $this;
    }
}
