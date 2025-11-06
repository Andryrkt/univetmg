<?php

namespace App\Entity\Unite;

use App\Entity\Produit\Produit;
use App\Repository\Unite\UniteConversionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UniteConversionRepository::class)]
class UniteConversion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $facteur = null;

    #[ORM\ManyToOne(inversedBy: 'uniteConversions')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'uniteConversions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Unite $uniteSource = null;

    #[ORM\ManyToOne(inversedBy: 'uniteConversions')]
    private ?Unite $uniteCible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacteur(): ?float
    {
        return $this->facteur;
    }

    public function setFacteur(float $facteur): static
    {
        $this->facteur = $facteur;

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

    public function getUniteSource(): ?Unite
    {
        return $this->uniteSource;
    }

    public function setUniteSource(?Unite $uniteSource): static
    {
        $this->uniteSource = $uniteSource;

        return $this;
    }

    public function getUniteCible(): ?Unite
    {
        return $this->uniteCible;
    }

    public function setUniteCible(?Unite $uniteCible): static
    {
        $this->uniteCible = $uniteCible;

        return $this;
    }
}
