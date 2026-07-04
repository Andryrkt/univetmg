<?php

namespace App\Entity\Stock;

use App\Entity\User;
use App\Enum\TypeMouvement;
use App\Repository\Stock\MouvementStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MouvementStock
{
    use \App\Entity\Trait\TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: TypeMouvement::class)]
    private ?TypeMouvement $type = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motif = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column]
    private ?float $stockAvant = null;

    #[ORM\Column]
    private ?float $stockApres = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementsStock')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lot $lot = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeMouvement
    {
        return $this->type;
    }

    public function setType(TypeMouvement $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(?string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStockAvant(): ?float
    {
        return $this->stockAvant;
    }

    public function setStockAvant(float $stockAvant): static
    {
        $this->stockAvant = $stockAvant;

        return $this;
    }

    public function getStockApres(): ?float
    {
        return $this->stockApres;
    }

    public function setStockApres(float $stockApres): static
    {
        $this->stockApres = $stockApres;

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): static
    {
        $this->lot = $lot;

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

    /**
     * Helper method to get the related Product via the Lot.
     */
    public function getProduit(): ?\App\Entity\Produit\Produit
    {
        return $this->lot?->getProduit();
    }
}
