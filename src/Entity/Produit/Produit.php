<?php

namespace App\Entity\Produit;

use App\Entity\Admin\Fournisseur;
use App\Entity\Stock\Lot;
use App\Entity\Stock\MouvementStock;
use App\Entity\Unite\Conditionnement;
use App\Entity\Unite\Unite;
use App\Repository\Produit\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit
{
    use \App\Entity\Trait\TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $code = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?float $stockMinimum = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixVente = null;

    /**
     * @var Collection<int, Conditionnement>
     */
    #[ORM\OneToMany(targetEntity: Conditionnement::class, mappedBy: 'produit', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conditionnements;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Unite $uniteDeBase = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Fournisseur $fournisseur = null;

    /**
     * @var Collection<int, Lot>
     */
    #[ORM\OneToMany(targetEntity: Lot::class, mappedBy: 'produit', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lots;

    public function __construct()
    {
        $this->conditionnements = new ArrayCollection();
        $this->lots = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function generateCode(): void
    {
        if (empty($this->code)) {
            $prefix = 'PROD';
            
            if ($this->categorie) {
                $categorie = $this->categorie;
                $parent = $categorie->getParent();
                
                if ($parent) {
                    // Si un parent existe : ABBR_PARENT/ABBR_ENFANT
                    $prefixParent = $parent->getAbbreviation() ?: strtoupper(substr($parent->getNom(), 0, 3));
                    $prefixEnfant = $categorie->getAbbreviation() ?: strtoupper(substr($categorie->getNom(), 0, 3));
                    $prefix = $prefixParent . '/' . $prefixEnfant;
                } else {
                    // Si pas de parent : ABBR_CATEGORIE
                    $prefix = $categorie->getAbbreviation() ?: strtoupper(substr($categorie->getNom(), 0, 3));
                }
            }
            
            $this->code = $prefix . '-' . strtoupper(substr(uniqid(), -4));
        }
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

    public function getStockMinimum(): ?float
    {
        return $this->stockMinimum;
    }

    public function setStockMinimum(float $stockMinimum): static
    {
        $this->stockMinimum = $stockMinimum;

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

    /**
     * @return Collection<int, Conditionnement>
     */
    public function getConditionnements(): Collection
    {
        return $this->conditionnements;
    }

    public function addConditionnement(Conditionnement $conditionnement): static
    {
        if (!$this->conditionnements->contains($conditionnement)) {
            $this->conditionnements->add($conditionnement);
            $conditionnement->setProduit($this);
        }

        return $this;
    }

    public function removeConditionnement(Conditionnement $conditionnement): static
    {
        if ($this->conditionnements->removeElement($conditionnement)) {
            // set the owning side to null (unless already changed)
            if ($conditionnement->getProduit() === $this) {
                $conditionnement->setProduit(null);
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

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    /**
     * @return Collection<int, Lot>
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): static
    {
        if (!$this->lots->contains($lot)) {
            $this->lots->add($lot);
            $lot->setProduit($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): static
    {
        if ($this->lots->removeElement($lot)) {
            // set the owning side to null (unless already changed)
            if ($lot->getProduit() === $this) {
                $lot->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * Calcule la quantité totale en stock en additionnant les quantités de tous les lots.
     */
    public function getQuantiteEnStock(): float
    {
        $total = 0.0;
        foreach ($this->lots as $lot) {
            $total += $lot->getQuantite();
        }
        return $total;
    }
}
