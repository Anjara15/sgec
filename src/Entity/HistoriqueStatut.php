<?php

namespace App\Entity;

use App\Enum\CourrierStatut;
use App\Repository\HistoriqueStatutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueStatutRepository::class)]
#[ORM\Table(name: 'historique_statut')]
class HistoriqueStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Courrier::class, inversedBy: 'historiquesStatut')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Courrier $courrier = null;

    #[ORM\Column(length: 30, enumType: CourrierStatut::class)]
    private CourrierStatut $ancienStatut = CourrierStatut::Brouillon;

    #[ORM\Column(length: 30, enumType: CourrierStatut::class)]
    private CourrierStatut $nouveauStatut = CourrierStatut::Enregistre;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $auteur = null;

    #[ORM\Column]
    private \DateTimeImmutable $dateChangement;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motif = null;

    public function __construct()
    {
        $this->dateChangement = new \DateTimeImmutable();
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

    public function getAncienStatut(): CourrierStatut
    {
        return $this->ancienStatut;
    }

    public function setAncienStatut(CourrierStatut $ancienStatut): static
    {
        $this->ancienStatut = $ancienStatut;

        return $this;
    }

    public function getNouveauStatut(): CourrierStatut
    {
        return $this->nouveauStatut;
    }

    public function setNouveauStatut(CourrierStatut $nouveauStatut): static
    {
        $this->nouveauStatut = $nouveauStatut;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getDateChangement(): \DateTimeImmutable
    {
        return $this->dateChangement;
    }

    public function setDateChangement(\DateTimeImmutable $dateChangement): static
    {
        $this->dateChangement = $dateChangement;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }
}
