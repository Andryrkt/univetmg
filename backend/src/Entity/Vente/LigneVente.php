<?php

namespace App\Entity\Vente;

use App\Entity\Produit\Produit;
use App\Entity\Unite\Unite;
use App\Repository\Vente\LigneVenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneVenteRepository::class)]
class LigneVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ligneVentes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vente $vente = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)] // Nullable allowed initially for migration or drafts, but logic should enforce it
    private ?Unite $unite = null;

    #[ORM\Column(options: ['default' => 1])]
    private ?float $facteurConversion = 1.0;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixCatalogue = null;

    #[ORM\Column(nullable: true)]
    private ?float $tauxRemise = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $montantRemise = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typeRemise = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $sousTotal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): static
    {
        $this->vente = $vente;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getUnite(): ?Unite
    {
        return $this->unite;
    }

    public function setUnite(?Unite $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getFacteurConversion(): ?float
    {
        return $this->facteurConversion;
    }

    public function setFacteurConversion(float $facteurConversion): static
    {
        $this->facteurConversion = $facteurConversion;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;
        $this->calculateSousTotal();

        return $this;
    }

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        $this->calculateSousTotal();

        return $this;
    }

    public function getSousTotal(): ?string
    {
        return $this->sousTotal;
    }

    public function setSousTotal(string $sousTotal): static
    {
        $this->sousTotal = $sousTotal;

        return $this;
    }

    public function getPrixCatalogue(): ?string
    {
        return $this->prixCatalogue;
    }

    public function setPrixCatalogue(?string $prixCatalogue): static
    {
        $this->prixCatalogue = $prixCatalogue;

        return $this;
    }

    public function getTauxRemise(): ?float
    {
        return $this->tauxRemise;
    }

    public function setTauxRemise(?float $tauxRemise): static
    {
        $this->tauxRemise = $tauxRemise;

        return $this;
    }

    public function getMontantRemise(): ?string
    {
        return $this->montantRemise;
    }

    public function setMontantRemise(?string $montantRemise): static
    {
        $this->montantRemise = $montantRemise;

        return $this;
    }

    public function getTypeRemise(): ?string
    {
        return $this->typeRemise;
    }

    public function setTypeRemise(?string $typeRemise): static
    {
        $this->typeRemise = $typeRemise;

        return $this;
    }

    private function calculateSousTotal(): void
    {
        if ($this->quantite !== null && $this->prixUnitaire !== null) {
            $this->sousTotal = (string) ($this->quantite * (float) $this->prixUnitaire);
        }
    }
}

