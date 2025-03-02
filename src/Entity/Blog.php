<?php

namespace App\Entity;

use App\Enum\EtatEnum;
use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Constraints as Assert; 

#[ORM\Entity(repositoryClass: BlogRepository::class)]
#[ORM\HasLifecycleCallbacks] // Enable lifecycle callbacks
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    private ?string $Contenu = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Ce champ ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/[a-zA-Z]/", 
        message: "Le champ doit contenir au moins une lettre."
    )]
    private ?string $Titre = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $rate = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $rateCount = 0; // Count of ratings

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'blog')]
    private Collection $listeCommentaires;

    #[ORM\Column(length: 255, enumType: EtatEnum::class, options: ["default" => "enAttente"])]
    private EtatEnum $statut = EtatEnum::enAttente;

    #[ORM\ManyToOne(inversedBy: 'blogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null; // Automatically set on creation


    #[ORM\ManyToMany(targetEntity: User::class)]
#[ORM\JoinTable(name: 'blog_user_ratings')]
private Collection $ratedByUsers;

public function __construct()
{
    $this->listeCommentaires = new ArrayCollection();
    $this->ratedByUsers = new ArrayCollection(); // Initialize the collection
}

// Add a method to check if a user has rated the blog
public function hasUserRated(User $user): bool
{
    return $this->ratedByUsers->contains($user);
}

// Add a method to add a user to the ratedByUsers collection
public function addRatedByUser(User $user): self
{
    if (!$this->ratedByUsers->contains($user)) {
        $this->ratedByUsers->add($user);
    }
    return $this;
}


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getContenu(): ?string
    {
        return $this->Contenu;
    }

    public function setContenu(string $Contenu): static
    {
        $this->Contenu = $Contenu;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): static
    {
        $this->Titre = $Titre;
        return $this;
    }

    public function getRateCount(): int
    {
        return $this->rateCount;
    }

    public function getRate(): float
    {
        return $this->rateCount > 0 ? $this->rate / $this->rateCount : 0;
    }

    public function addRate(int $rating): self
    {
        if ($rating >= 0 && $rating <= 5) {
            $this->rate += $rating;
            $this->rateCount++;
        }
        return $this;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;
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

    /**
     * @return Collection<int, Commentaire>
     */
    public function getListeCommentaires(): Collection
    {
        return $this->listeCommentaires;
    }

    public function addListeCommentaire(Commentaire $listeCommentaire): static
    {
        if (!$this->listeCommentaires->contains($listeCommentaire)) {
            $this->listeCommentaires->add($listeCommentaire);
            $listeCommentaire->setBlog($this);
        }
        return $this;
    }

    public function removeListeCommentaire(Commentaire $listeCommentaire): static
    {
        if ($this->listeCommentaires->removeElement($listeCommentaire)) {
            // Set the owning side to null (unless already changed)
            if ($listeCommentaire->getBlog() === $this) {
                $listeCommentaire->setBlog(null);
            }
        }
        return $this;
    }

    public function getStatut(): EtatEnum
    {
        return $this->statut;
    }

    public function setStatut(EtatEnum $statut): self
    {
        $this->statut = $statut;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
}