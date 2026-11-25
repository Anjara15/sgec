<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $destinataire = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $typeEvenement = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $message = '';

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateLecture = null;

    #[ORM\ManyToOne(targetEntity: Courrier::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Courrier $lienCourrier = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    public function setDestinataire(?User $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getTypeEvenement(): string
    {
        return $this->typeEvenement;
    }

    public function setTypeEvenement(string $typeEvenement): static
    {
        $this->typeEvenement = $typeEvenement;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateLecture(): ?\DateTimeImmutable
    {
        return $this->dateLecture;
    }

    public function setDateLecture(?\DateTimeImmutable $dateLecture): static
    {
        $this->dateLecture = $dateLecture;

        return $this;
    }

    public function getLienCourrier(): ?Courrier
    {
        return $this->lienCourrier;
    }

    public function setLienCourrier(?Courrier $lienCourrier): static
    {
        $this->lienCourrier = $lienCourrier;

        return $this;
    }

    public function isLue(): bool
    {
        return $this->dateLecture !== null;
    }

    public function marquerLue(): static
    {
        $this->dateLecture = new \DateTimeImmutable();

        return $this;
    }
}
