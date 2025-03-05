<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Validator\Constraints as Assert;


//#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['tel'], message: 'Ce numéro est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse e-mail '{{ value }}' n'est pas valide.")]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 30, minMessage: "Votre nom doit contenir au moins {{ limit }} caractères.", maxMessage: "Votre nom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom ne peut pas être vide.")]
    #[Assert\Length(min: 3, max: 30, minMessage: "Votre prénom doit contenir au moins {{ limit }} caractères.", maxMessage: "Votre prénom ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $prenom = null;


    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateNaissance;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;


    #[ORM\Column(type: 'string', length: 8, unique: true)]
    #[Assert\NotBlank(message: "Le téléphone ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^\d{8}$/", // Regex pour 8 chiffres
        message: "Le numéro de téléphone doit comporter exactement 8 chiffres."
    )]


    private ?string $tel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'boolean')]
    private $isBanned = false;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastConnexion;

    private ?string $authCode = null;

    /**
     * @var Collection<int, Offre>
     */
    #[ORM\OneToMany(mappedBy: 'annonceOwner', targetEntity: Offre::class, orphanRemoval: true)]
    private Collection $offres;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'users')]
    private Collection $conversations;

    /**
     * @var Collection<int, Souk>
     */
    #[ORM\ManyToMany(targetEntity: Souk::class, mappedBy: 'participant')]
    private Collection $souks;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Product::class)]
    private Collection $products;

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->addUser($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            $conversation->removeUser($this);
        }

        return $this;
    }
    /** 
     * @var Collection<int, Favoris>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Favoris::class)]
    private Collection $favoris;

    /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reclamation::class, orphanRemoval: true)]
    private Collection $reclamations;


    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(mappedBy: 'idRecepteur', targetEntity: Rating::class)]
    private Collection $ratings;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->dateNaissance = new \DateTime(); // Exemple de valeur par défaut : la date actuelle
        $this->offres = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->souks = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials()
    {
        // Effacer les données sensibles si nécessaire
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function isEmailAuthEnabled(): bool
    {
        return true; // This can be a persisted field to switch email code authentication on/off
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    /**
     * Get the email authentication code (for 2FA).
     * Throws an exception if the authCode is not set.
     *
     * @throws \LogicException if the auth code has not been set.
     */
    public function getEmailAuthCode(): string
    {
        if (null === $this->authCode) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->authCode;
    }

    /**
     * Set the email authentication code (for 2FA).
     * This should only be set when generating a new code for 2FA.
     */
    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastConnexion(): ?\DateTime
    {
        return $this->lastConnexion;
    }

    public function setLastConnexion(?\DateTime $lastConnexion): self
    {
        $this->lastConnexion = $lastConnexion;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateNaissance(): \DateTimeInterface
    {
        // Si la date n'est pas définie, tu pourrais vouloir lever une exception ou la créer automatiquement

        return $this->dateNaissance;
    }


    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }


    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;
        return $this;
    }


    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }
    /*
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori)
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setUser($this);
        }
    }

    /** 
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setIdRecepteur($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getUser() === $this) {
                $favori->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setUser($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation)
    {
        if ($this->reclamations->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getUser() === $this) {
                $reclamation->setUser(null);
            }
        }
    }


    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getIdRecepteur() === $this) {
                $rating->setIdRecepteur(null);
            }
        }

        return $this;
    }


    public function addOffre(Offre $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setAnnonceOwner($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getAnnonceOwner() === $this) {
                $offre->setAnnonceOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Souk>
     */
    public function getSouks(): Collection
    {
        return $this->souks;
    }

    public function addSouk(Souk $souk): static
    {
        if (!$this->souks->contains($souk)) {
            $this->souks->add($souk);
            $souk->addParticipant($this);
        }

        return $this;
    }

    public function removeSouk(Souk $souk): static
    {
        if ($this->souks->removeElement($souk)) {
            $souk->removeParticipant($this);
        }

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
            $product->setOwner($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }

        return $this;
    }


    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;
        return $this;
    }
}