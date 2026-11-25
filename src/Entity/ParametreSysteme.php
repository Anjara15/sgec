<?php

namespace App\Entity;

use App\Repository\ParametreSystemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParametreSystemeRepository::class)]
#[ORM\Table(name: 'parametre_systeme')]
#[ORM\UniqueConstraint(name: 'UNIQ_PARAM_CLE', fields: ['cle'])]
#[UniqueEntity(fields: ['cle'], message: 'Cette clé existe déjà.')]
class ParametreSysteme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $cle = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $valeur = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCle(): string
    {
        return $this->cle;
    }

    public function setCle(string $cle): static
    {
        $this->cle = $cle;

        return $this;
    }

    public function getValeur(): string
    {
        return $this->valeur;
    }

    public function setValeur(string $valeur): static
    {
        $this->valeur = $valeur;

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

    public function __toString(): string
    {
        return $this->cle;
    }
}
