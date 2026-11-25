<?php

namespace App\Entity;

use App\Enum\UserStatut;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $nom = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $prenom = '';

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column]
    private string $password = '';

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Service $service = null;

    #[ORM\Column(length: 20, enumType: UserStatut::class)]
    private UserStatut $statut = UserStatut::Actif;

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $customRoles;

    /** @var Collection<int, Courrier> */
    #[ORM\OneToMany(mappedBy: 'createur', targetEntity: Courrier::class)]
    private Collection $courriersCrees;

    /** @var Collection<int, Notification> */
    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Notification::class)]
    private Collection $notifications;

    /** @var Collection<int, JournalActivite> */
    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: JournalActivite::class)]
    private Collection $journalActivites;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->customRoles = new ArrayCollection();
        $this->courriersCrees = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->journalActivites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = array_values($roles);

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getStatut(): UserStatut
    {
        return $this->statut;
    }

    public function setStatut(UserStatut $statut): static
    {
        $this->statut = $statut;

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

    /** @return Collection<int, Role> */
    public function getCustomRoles(): Collection
    {
        return $this->customRoles;
    }

    public function addCustomRole(Role $role): static
    {
        if (!$this->customRoles->contains($role)) {
            $this->customRoles->add($role);
        }

        return $this;
    }

    public function removeCustomRole(Role $role): static
    {
        $this->customRoles->removeElement($role);

        return $this;
    }

    /** @return Collection<int, Courrier> */
    public function getCourriersCrees(): Collection
    {
        return $this->courriersCrees;
    }

    /** @return Collection<int, Notification> */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /** @return Collection<int, JournalActivite> */
    public function getJournalActivites(): Collection
    {
        return $this->journalActivites;
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
    }

    public function hasPermission(string $permission): bool
    {
        if (\in_array('ROLE_ADMIN', $this->getRoles(), true)) {
            return true;
        }

        foreach ($this->customRoles as $role) {
            if (\in_array($permission, $role->getPermissions(), true)) {
                return true;
            }
        }

        return false;
    }
}
