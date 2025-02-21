<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre de l'événement est obligatoire.")]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "La description doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $description = null;

    #[ORM\Column(type: "date", nullable: false)]
    #[Assert\NotBlank(message:"La date de debut est obligatoire.")]
    #[Assert\Type(type:"\DateTimeInterface", message:"Veuillez entrer une date valide.")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de début doit être aujourd'hui ou dans le futur."
    )]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: "date", nullable: false)]
    #[Assert\NotBlank(message:"La date de fin est obligatoire.")]
    #[Assert\Type(type:"\DateTimeInterface", message:"Veuillez entrer une date valide.")]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "dateDebut",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"Le nombre de participant est obligatiore.")]
    #[Assert\Positive(message: "Le nombre maximum de participants doit être un nombre positif.")]
    private ?int $maxParticipant = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $sessions;

    /**
     * @var Collection<int, ParticipantEvent>
     */
    #[ORM\OneToMany(targetEntity: ParticipantEvent::class, mappedBy: 'event')]
    private Collection $participantEvents;

    /**
     * @var Collection<int, Certificate>
     */
    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'event')]
    private Collection $certificates;



    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $orgniser = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

   
    


    private Collection $eventId;

    #[ORM\Column(length: 255)]
  
    private ?string $image = null;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->participantEvents = new ArrayCollection();
        $this->certificates = new ArrayCollection();
        $this->eventId = new ArrayCollection();
        $this->dateDebut = new \DateTime();
        $this->dateFin = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }



    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setEvent($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getEvent() === $this) {
                $session->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ParticipantEvent>
     */
    public function getParticipantEvents(): Collection
    {
        return $this->participantEvents;
    }

    public function addParticipantEvent(ParticipantEvent $participantEvent): static
    {
        if (!$this->participantEvents->contains($participantEvent)) {
            $this->participantEvents->add($participantEvent);
            $participantEvent->setEvent($this);
        }

        return $this;
    }

    public function removeParticipantEvent(ParticipantEvent $participantEvent): static
    {
        if ($this->participantEvents->removeElement($participantEvent)) {
            // set the owning side to null (unless already changed)
            if ($participantEvent->getEvent() === $this) {
                $participantEvent->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): static
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setEvent($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): static
    {
        if ($this->certificates->removeElement($certificate)) {
            // set the owning side to null (unless already changed)
            if ($certificate->getEvent() === $this) {
                $certificate->setEvent(null);
            }
        }

        return $this;
    }

    public function getOrgniser(): ?User
    {
        return $this->orgniser;
    }

    public function setOrgniser(?User $orgniser): static
    {
        $this->orgniser = $orgniser;

        return $this;
    }

    public function decrementParticipant(): void
    {
        if ($this->maxParticipant > 0) {
            $this->maxParticipant--;
        } else {
            throw new \Exception('This event has reached its maximum participant limit.');
        }
    }

    public function isFullyBooked(): bool
    {
        return $this->maxParticipant <= 0;
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

 


    public function getEventId(): Collection
    {
        return $this->eventId;
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



  
}
