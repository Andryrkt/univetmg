<?php

namespace App\Entity\Unite;

use App\Repository\Unite\ConversionStandardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConversionStandardRepository::class)]
#[ORM\Table(name: 'conversion_standard')]
#[UniqueEntity(
    fields: ['uniteOrigine', 'uniteCible'],
    message: 'Cette conversion existe déjà.'
)]
#[ORM\HasLifecycleCallbacks]
class ConversionStandard
{
    use \App\Entity\Trait\TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Unite $uniteOrigine = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Unite $uniteCible = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le facteur doit être un nombre positif.')]
    private ?float $facteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniteOrigine(): ?Unite
    {
        return $this->uniteOrigine;
    }

    public function setUniteOrigine(?Unite $uniteOrigine): static
    {
        $this->uniteOrigine = $uniteOrigine;

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

    public function getFacteur(): ?float
    {
        return $this->facteur;
    }

    public function setFacteur(float $facteur): static
    {
        $this->facteur = $facteur;

        return $this;
    }
}
