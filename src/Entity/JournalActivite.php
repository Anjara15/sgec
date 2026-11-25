<?php

namespace App\Entity;

use App\Repository\JournalActiviteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JournalActiviteRepository::class)]
#[ORM\Table(name: 'journal_activite')]
class JournalActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'journalActivites')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $utilisateur = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private string $action = '';

    #[ORM\Column(length: 100)]
    private string $typeObjet = '';

    #[ORM\Column(nullable: true)]
    private ?int $objetId = null;

    #[ORM\Column]
    private \DateTimeImmutable $date;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $infos = [];

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getTypeObjet(): string
    {
        return $this->typeObjet;
    }

    public function setTypeObjet(string $typeObjet): static
    {
        $this->typeObjet = $typeObjet;

        return $this;
    }

    public function getObjetId(): ?int
    {
        return $this->objetId;
    }

    public function setObjetId(?int $objetId): static
    {
        $this->objetId = $objetId;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getInfos(): array
    {
        return $this->infos;
    }

    /** @param array<string, mixed> $infos */
    public function setInfos(array $infos): static
    {
        $this->infos = $infos;

        return $this;
    }
}
