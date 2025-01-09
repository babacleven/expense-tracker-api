<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user","depense_show", "data_select"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user","depense_show", "data_select"])]
    private ?string $roles = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user","api_administration_show"])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user","depense_show", "data_select"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(["user","api_administration_show"])]
    private ?string $fonction = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Depense>
     */
    #[ORM\OneToMany(targetEntity: Depense::class, mappedBy: 'user')]
    private Collection $id_user;

    // #[ORM\ManyToOne(inversedBy: 'user')]
    // private ?Partenaire $id_partenaire = null;

    /**
     * @var Collection<int, Caisse>
     */
    public function __construct()
    {
        $this->id_user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        // Convertit la chaîne de caractères en tableau si nécessaire
        return $this->roles ? explode(',', $this->roles) : ['ROLE_USER'];
    }

    public function setRoles(string $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Cette méthode est utilisée pour nettoyer les informations sensibles de l'utilisateur, si nécessaire.
        // Par exemple : $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        // Retournez ici l'identifiant unique de l'utilisateur (par exemple, l'email ou le nom d'utilisateur)
        return $this->telephone; // ou $this->username si votre identifiant est un nom d'utilisateur
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getIdUser(): Collection
    {
        return $this->id_user;
    }

    public function addIdUser(Depense $idUser): static
    {
        if (!$this->id_user->contains($idUser)) {
            $this->id_user->add($idUser);
            $idUser->setUser($this);
        }

        return $this;
    }

    public function removeIdUser(Depense $idUser): static
    {
        if ($this->id_user->removeElement($idUser)) {
            // set the owning side to null (unless already changed)
            if ($idUser->getUser() === $this) {
                $idUser->setUser(null);
            }
        }

        return $this;
    }
}
