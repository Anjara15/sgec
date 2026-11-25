<?php

namespace App\Entity;

use App\Enum\Confidentialite;
use App\Enum\CourrierStatut;
use App\Enum\CourrierType;
use App\Enum\Priorite;
use App\Repository\CourrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourrierRepository::class)]
#[ORM\Table(name: 'courrier')]
#[ORM\UniqueConstraint(name: 'UNIQ_COURRIER_REF', fields: ['reference'])]
#[UniqueEntity(fields: ['reference'], message: 'Cette référence existe déjà.')]
class Courrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $reference = '';

    #[ORM\Column(length: 20, enumType: CourrierType::class)]
    private CourrierType $type = CourrierType::Entrant;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $objet = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateDocument = null;

    #[ORM\Column]
    private \DateTimeImmutable $dateEnregistrement;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $expediteur = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $destinataire = '';

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $serviceOrigine = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $serviceDestinataire = null;

    #[ORM\Column(length: 20, enumType: Priorite::class)]
    private Priorite $priorite = Priorite::Normale;

    #[ORM\Column(length: 30, enumType: Confidentialite::class)]
    private Confidentialite $confidentialite = Confidentialite::Interne;

    #[ORM\Column(length: 30, enumType: CourrierStatut::class)]
    private CourrierStatut $statut = CourrierStatut::Enregistre;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courriersCrees')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $createur = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $agentResponsable = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateArchivage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motifValidation = null;

    /** @var Collection<int, PieceJointe> */
    #[ORM\OneToMany(mappedBy: 'courrier', targetEntity: PieceJointe::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $piecesJointes;

    /** @var Collection<int, Transmission> */
    #[ORM\OneToMany(mappedBy: 'courrier', targetEntity: Transmission::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['dateTransmission' => 'DESC'])]
    private Collection $transmissions;

    /** @var Collection<int, HistoriqueStatut> */
    #[ORM\OneToMany(mappedBy: 'courrier', targetEntity: HistoriqueStatut::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['dateChangement' => 'DESC'])]
    private Collection $historiquesStatut;

    public function __construct()
    {
        $this->dateEnregistrement = new \DateTimeImmutable();
        $this->piecesJointes = new ArrayCollection();
        $this->transmissions = new ArrayCollection();
        $this->historiquesStatut = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getType(): CourrierType
    {
        return $this->type;
    }

    public function setType(CourrierType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getObjet(): string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): static
    {
        $this->objet = $objet;

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

    public function getDateDocument(): ?\DateTimeImmutable
    {
        return $this->dateDocument;
    }

    public function setDateDocument(?\DateTimeImmutable $dateDocument): static
    {
        $this->dateDocument = $dateDocument;

        return $this;
    }

    public function getDateEnregistrement(): \DateTimeImmutable
    {
        return $this->dateEnregistrement;
    }

    public function setDateEnregistrement(\DateTimeImmutable $dateEnregistrement): static
    {
        $this->dateEnregistrement = $dateEnregistrement;

        return $this;
    }

    public function getExpediteur(): string
    {
        return $this->expediteur;
    }

    public function setExpediteur(string $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinataire(): string
    {
        return $this->destinataire;
    }

    public function setDestinataire(string $destinataire): static
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

    public function getPriorite(): Priorite
    {
        return $this->priorite;
    }

    public function setPriorite(Priorite $priorite): static
    {
        $this->priorite = $priorite;

        return $this;
    }

    public function getConfidentialite(): Confidentialite
    {
        return $this->confidentialite;
    }

    public function setConfidentialite(Confidentialite $confidentialite): static
    {
        $this->confidentialite = $confidentialite;

        return $this;
    }

    public function getStatut(): CourrierStatut
    {
        return $this->statut;
    }

    public function setStatut(CourrierStatut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;

        return $this;
    }

    public function getAgentResponsable(): ?User
    {
        return $this->agentResponsable;
    }

    public function setAgentResponsable(?User $agentResponsable): static
    {
        $this->agentResponsable = $agentResponsable;

        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeImmutable $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getDateArchivage(): ?\DateTimeImmutable
    {
        return $this->dateArchivage;
    }

    public function setDateArchivage(?\DateTimeImmutable $dateArchivage): static
    {
        $this->dateArchivage = $dateArchivage;

        return $this;
    }

    public function getMotifValidation(): ?string
    {
        return $this->motifValidation;
    }

    public function setMotifValidation(?string $motifValidation): static
    {
        $this->motifValidation = $motifValidation;

        return $this;
    }

    /** @return Collection<int, PieceJointe> */
    public function getPiecesJointes(): Collection
    {
        return $this->piecesJointes;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): static
    {
        if (!$this->piecesJointes->contains($pieceJointe)) {
            $this->piecesJointes->add($pieceJointe);
            $pieceJointe->setCourrier($this);
        }

        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): static
    {
        if ($this->piecesJointes->removeElement($pieceJointe) && $pieceJointe->getCourrier() === $this) {
            $pieceJointe->setCourrier(null);
        }

        return $this;
    }

    /** @return Collection<int, Transmission> */
    public function getTransmissions(): Collection
    {
        return $this->transmissions;
    }

    public function addTransmission(Transmission $transmission): static
    {
        if (!$this->transmissions->contains($transmission)) {
            $this->transmissions->add($transmission);
            $transmission->setCourrier($this);
        }

        return $this;
    }

    /** @return Collection<int, HistoriqueStatut> */
    public function getHistoriquesStatut(): Collection
    {
        return $this->historiquesStatut;
    }

    public function addHistoriqueStatut(HistoriqueStatut $historique): static
    {
        if (!$this->historiquesStatut->contains($historique)) {
            $this->historiquesStatut->add($historique);
            $historique->setCourrier($this);
        }

        return $this;
    }

    public function isArchive(): bool
    {
        return $this->statut === CourrierStatut::Archive;
    }

    public function __toString(): string
    {
        return $this->reference;
    }
}
