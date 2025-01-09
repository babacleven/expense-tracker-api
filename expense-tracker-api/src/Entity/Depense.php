<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DepenseRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["depense_show"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["depense_show"])]

    private ?string $montant = null;

    #[ORM\Column(length: 255)]
    #[Groups(["depense_show"])]
    private ?string $categories = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["depense_show"])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'id_user')]
    #[Groups(["depense_show", "user"])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(["depense_show"])]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getCategories(): ?string
    {
        return $this->categories;
    }

    public function setCategories(string $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
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
}
