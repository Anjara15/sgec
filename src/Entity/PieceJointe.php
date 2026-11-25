<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
#[ORM\Table(name: 'piece_jointe')]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $nomOriginal = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $nomStockage = '';

    #[ORM\Column(length: 150)]
    private string $typeMime = 'application/octet-stream';

    #[ORM\Column]
    private int $taille = 0;

    #[ORM\Column]
    private \DateTimeImmutable $dateAjout;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Courrier::class, inversedBy: 'piecesJointes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Courrier $courrier = null;

    public function __construct()
    {
        $this->dateAjout = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomOriginal(): string
    {
        return $this->nomOriginal;
    }

    public function setNomOriginal(string $nomOriginal): static
    {
        $this->nomOriginal = $nomOriginal;

        return $this;
    }

    public function getNomStockage(): string
    {
        return $this->nomStockage;
    }

    public function setNomStockage(string $nomStockage): static
    {
        $this->nomStockage = $nomStockage;

        return $this;
    }

    public function getTypeMime(): string
    {
        return $this->typeMime;
    }

    public function setTypeMime(string $typeMime): static
    {
        $this->typeMime = $typeMime;

        return $this;
    }

    public function getTaille(): int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getDateAjout(): \DateTimeImmutable
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeImmutable $dateAjout): static
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
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
}
