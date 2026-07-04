<?php

namespace App\Entity\Vente;

use App\Entity\User;
use App\Enum\StatutVente;
use App\Repository\Vente\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $numeroFacture = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateVente = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = null;

    #[ORM\Column(length: 50, enumType: StatutVente::class)]
    private ?StatutVente $statut = null;

    #[ORM\OneToMany(mappedBy: 'vente', targetEntity: LigneVente::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ligneVentes;

    public function __construct()
    {
        $this->dateVente = new \DateTimeImmutable();
        $this->ligneVentes = new ArrayCollection();
        $this->statut = StatutVente::BROUILLON;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getDateVente(): ?\DateTimeImmutable
    {
        return $this->dateVente;
    }

    public function setDateVente(\DateTimeImmutable $dateVente): static
    {
        $this->dateVente = $dateVente;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

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

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getStatut(): ?StatutVente
    {
        return $this->statut;
    }

    public function setStatut(StatutVente $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, LigneVente>
     */
    public function getLigneVentes(): Collection
    {
        return $this->ligneVentes;
    }

    public function addLigneVente(LigneVente $ligneVente): static
    {
        if (!$this->ligneVentes->contains($ligneVente)) {
            $this->ligneVentes->add($ligneVente);
            $ligneVente->setVente($this);
            $this->recalculateTotal();
        }

        return $this;
    }

    public function removeLigneVente(LigneVente $ligneVente): static
    {
        if ($this->ligneVentes->removeElement($ligneVente)) {
            // set the owning side to null (unless already changed)
            if ($ligneVente->getVente() === $this) {
                $ligneVente->setVente(null);
            }
            $this->recalculateTotal();
        }

        return $this;
    }

    public function recalculateTotal(): void
    {
        $total = 0;
        foreach ($this->ligneVentes as $ligne) {
            $total += (float) $ligne->getSousTotal();
        }
        $this->total = (string) $total;
    }
}
