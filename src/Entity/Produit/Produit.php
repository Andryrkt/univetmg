<?php

namespace App\Entity\Produit;

use App\Entity\Admin\Fournisseur;
use App\Entity\Unite\Unite;
use App\Entity\Unite\UniteConversion;
use App\Repository\Produit\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $code = null;

    #[ORM\Column]
    private ?float $stockInitial = null;

    #[ORM\Column]
    private ?float $stockMinimum = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixAchat = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixVente = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $datePeremption = null;

    /**
     * @var Collection<int, UniteConversion>
     */
    #[ORM\OneToMany(targetEntity: UniteConversion::class, mappedBy: 'produit')]
    private Collection $uniteConversions;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Unite $uniteDeBase = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Fournisseur $fournisseur = null;

    public function __construct()
    {
        $this->uniteConversions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getStockInitial(): ?float
    {
        return $this->stockInitial;
    }

    public function setStockInitial(float $stockInitial): static
    {
        $this->stockInitial = $stockInitial;

        return $this;
    }

    public function getStockMinimum(): ?float
    {
        return $this->stockMinimum;
    }

    public function setStockMinimum(float $stockMinimum): static
    {
        $this->stockMinimum = $stockMinimum;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getPrixVente(): ?float
    {
        return $this->prixVente;
    }

    public function setPrixVente(?float $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function getDatePeremption(): ?\DateTime
    {
        return $this->datePeremption;
    }

    public function setDatePeremption(?\DateTime $datePeremption): static
    {
        $this->datePeremption = $datePeremption;

        return $this;
    }

    /**
     * @return Collection<int, UniteConversion>
     */
    public function getUniteConversions(): Collection
    {
        return $this->uniteConversions;
    }

    public function addUniteConversion(UniteConversion $uniteConversion): static
    {
        if (!$this->uniteConversions->contains($uniteConversion)) {
            $this->uniteConversions->add($uniteConversion);
            $uniteConversion->setProduit($this);
        }

        return $this;
    }

    public function removeUniteConversion(UniteConversion $uniteConversion): static
    {
        if ($this->uniteConversions->removeElement($uniteConversion)) {
            // set the owning side to null (unless already changed)
            if ($uniteConversion->getProduit() === $this) {
                $uniteConversion->setProduit(null);
            }
        }

        return $this;
    }

    public function getUniteDeBase(): ?Unite
    {
        return $this->uniteDeBase;
    }

    public function setUniteDeBase(?Unite $uniteDeBase): static
    {
        $this->uniteDeBase = $uniteDeBase;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }
}
