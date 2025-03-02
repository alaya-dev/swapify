<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "L'heure de début est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Veuillez entrer une date et une heure valides.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startHour = null;

    #[Assert\NotBlank(message: "L'heure de fin est obligatoire.")]
    #[Assert\Type(type: "\DateTimeInterface", message: "Veuillez entrer une date et une heure valides.")]
    #[Assert\GreaterThan(
        propertyPath: "startHour",
        message: "L'heure de fin doit être postérieure à l'heure de début."
    )]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endHour = null;

    // #[ORM\Column]
    // private ?bool $sessionPresence = null;

    #[ORM\Column(length: 255)]
    private ?string $typeSession = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'session')]
    private Collection $attendances;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $objective = null;

    #[ORM\Column]
    private ?bool $meetingStarted = null;



    public function __construct()
    {
        $this->attendances = new ArrayCollection();
        $this->startHour = new \DateTime();
        $this->endHour = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(\DateTimeInterface $startHour): static
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(\DateTimeInterface $endHour): static
    {
        $this->endHour = $endHour;

        return $this;
    }

    // public function isSessionPresence(): ?bool
    // {
    //     return $this->sessionPresence;
    // }

    // public function setSessionPresence(bool $sessionPresence): static
    // {
    //     $this->sessionPresence = $sessionPresence;

    //     return $this;
    // }

    public function getTypeSession(): ?string
    {
        return $this->typeSession;
    }

    public function setTypeSession(string $typeSession): static
    {
        $this->typeSession = $typeSession;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setSession($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getSession() === $this) {
                $attendance->setSession(null);
            }
        }

        return $this;
    }

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    public function isMeetingStarted(): ?bool
    {
        return $this->meetingStarted;
    }

    public function setMeetingStarted(bool $meetingStarted): static
    {
        $this->meetingStarted = $meetingStarted;

        return $this;
    }

}
