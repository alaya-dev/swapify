<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    private ?Livreur $livreur = null;

    #[ORM\ManyToOne(inversedBy: 'livraisonExp')]
    private ?User $id_expediteur = null;

    #[ORM\ManyToOne(inversedBy: 'livraisonDis')]
    private ?User $id_distinataire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $localisation_expediteur_lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $localisation_expediteur_lng = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $localisation_destinataire_lat = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $localisation_destinataire_lng = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payment_exp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $payment_dist = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^\d{8}$/",
        message: "Le numéro de téléphone doit contenir exactement 8 chiffres."
    )]
    private ?int $TelephoneExpediteur = null;

    #[ORM\Column]
    #[Assert\Regex(
        pattern: "/^\d{4}$/",
        message: "Le code postale doit contenir exactement 4 chiffres."
    )]
     #[Assert\NotBlank(message: "La code postal ne peut pas être vide.")]
    private ?int $CodePostalExpediteur = null;

    #[ORM\Column(nullable: true)]
    private ?int $TelephoneDestinataire = null;


    #[ORM\Column(nullable: true)]
    private ?int $CodePostalDestinataire = null;

    #[Assert\NotBlank(message: "L 'adresse ne peut pas être vide.")]
    #[ORM\Column(length: 255)]
    private ?string $adresseExpediteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseDestiniataire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }

    public function setLivreur(?Livreur $livreur): static
    {
        $this->livreur = $livreur;

        return $this;
    }

    public function getIdExpediteur(): ?User
    {
        return $this->id_expediteur;
    }

    public function setIdExpediteur(?User $id_expediteur): static
    {
        $this->id_expediteur = $id_expediteur;

        return $this;
    }

    public function getIdDistinataire(): ?User
    {
        return $this->id_distinataire;
    }

    public function setIdDistinataire(?User $id_distinataire): static
    {
        $this->id_distinataire = $id_distinataire;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLocalisationExpediteurLat(): ?float
    {
        return $this->localisation_expediteur_lat;
    }

    public function setLocalisationExpediteurLat(float $localisation_expediteur_lat): static
    {
        $this->localisation_expediteur_lat = $localisation_expediteur_lat;

        return $this;
    }

    public function getLocalisationExpediteurLng(): ?float
    {
        return $this->localisation_expediteur_lng;
    }

    public function setLocalisationExpediteurLng(float $localisation_expediteur_lng): static
    {
        $this->localisation_expediteur_lng = $localisation_expediteur_lng;

        return $this;
    }

    public function getLocalisationDestinataireLat(): ?float
    {
        return $this->localisation_destinataire_lat;
    }

    public function setLocalisationDestinataireLat(float $localisation_destinataire_lat): static
    {
        $this->localisation_destinataire_lat = $localisation_destinataire_lat;

        return $this;
    }

    public function getLocalisationDestinataireLng(): ?float
    {
        return $this->localisation_destinataire_lng;
    }

    public function setLocalisationDestinataireLng(float $localisation_destinataire_lng): static
    {
        $this->localisation_destinataire_lng = $localisation_destinataire_lng;

        return $this;
    }

    public function getPaymentExp(): ?string
    {
        return $this->payment_exp;
    }

    public function setPaymentExp(?string $payment_exp): static
    {
        $this->payment_exp = $payment_exp;

        return $this;
    }

    public function getPaymentDist(): ?string
    {
        return $this->payment_dist;
    }

    public function setPaymentDist(?string $payment_dist): static
    {
        $this->payment_dist = $payment_dist;

        return $this;
    }

    public function getTelephoneExpediteur(): ?int
    {
        return $this->TelephoneExpediteur;
    }

    public function setTelephoneExpediteur(int $TelephoneExpediteur): static
    {
        $this->TelephoneExpediteur = $TelephoneExpediteur;

        return $this;
    }

    public function getCodePostalExpediteur(): ?int
    {
        return $this->CodePostalExpediteur;
    }

    public function setCodePostalExpediteur(int $CodePostalExpediteur): static
    {
        $this->CodePostalExpediteur = $CodePostalExpediteur;

        return $this;
    }

    public function getTelephoneDestinataire(): ?int
    {
        return $this->TelephoneDestinataire;
    }

    public function setTelephoneDestinataire(int $TelephoneDestinataire): static
    {
        $this->TelephoneDestinataire = $TelephoneDestinataire;

        return $this;
    }

    public function getCodePostalDestinataire(): ?int
    {
        return $this->CodePostalDestinataire;
    }

    public function setCodePostalDestinataire(int $CodePostalDestinataire): static
    {
        $this->CodePostalDestinataire = $CodePostalDestinataire;

        return $this;
    }

    public function getAdresseExpediteur(): ?string
    {
        return $this->adresseExpediteur;
    }

    public function setAdresseExpediteur(string $adresseExpediteur): static
    {
        $this->adresseExpediteur = $adresseExpediteur;

        return $this;
    }

    public function getAdresseDestiniataire(): ?string
    {
        return $this->adresseDestiniataire;
    }

    public function setAdresseDestiniataire(?string $adresseDestiniataire): static
    {
        $this->adresseDestiniataire = $adresseDestiniataire;

        return $this;
    }
}
