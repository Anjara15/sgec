<?php

namespace App\Entity;

use App\Enum\TransmissionEtat;
use App\Repository\TransmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransmissionRepository::class)]
#[ORM\Table(name: 'transmission')]
class Transmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Courrier::class, inversedBy: 'transmissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Courrier $courrier = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $expediteur = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $destinataire = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $serviceOrigine = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $serviceDestinataire = null;

    #[ORM\Column]
    private \DateTimeImmutable $dateTransmission;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateReception = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 20, enumType: TransmissionEtat::class)]
    private TransmissionEtat $etat = TransmissionEtat::Envoye;

    public function __construct()
    {
        $this->dateTransmission = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourrier(): ?Courrier
    {
        return $this->courrier;
    }

    public function setCourrier(?Courrier $courrier): static
    {
        $this->courrier = $courrier;

        return $this;
    }

    public function getExpediteur(): ?User
    {
        return $this->expediteur;
    }

    public function setExpediteur(?User $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
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

    public function getServiceOrigine(): ?Service
    {
        return $this->serviceOrigine;
    }

    public function setServiceOrigine(?Service $serviceOrigine): static
    {
        $this->serviceOrigine = $serviceOrigine;

        return $this;
    }

    public function getServiceDestinataire(): ?Service
    {
        return $this->serviceDestinataire;
    }

    public function setServiceDestinataire(?Service $serviceDestinataire): static
    {
        $this->serviceDestinataire = $serviceDestinataire;

        return $this;
    }

    public function getDateTransmission(): \DateTimeImmutable
    {
        return $this->dateTransmission;
    }

    public function setDateTransmission(\DateTimeImmutable $dateTransmission): static
    {
        $this->dateTransmission = $dateTransmission;

        return $this;
    }

    public function getDateReception(): ?\DateTimeImmutable
    {
        return $this->dateReception;
    }

    public function setDateReception(?\DateTimeImmutable $dateReception): static
    {
        $this->dateReception = $dateReception;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getEtat(): TransmissionEtat
    {
        return $this->etat;
    }

    public function setEtat(TransmissionEtat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
}
