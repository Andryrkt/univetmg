<?php

namespace App\Repository\Vente;

use App\Entity\Produit\Produit;
use App\Entity\Vente\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Promotion>
 */
class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    /**
     * Find active promotions at a given date
     *
     * @param \DateTimeInterface|null $date
     * @return Promotion[]
     */
    public function findActivePromotions(?\DateTimeInterface $date = null): array
    {
        $date = $date ?? new \DateTime();

        return $this->createQueryBuilder('p')
            ->andWhere('p.actif = :actif')
            ->andWhere('p.dateDebut <= :date')
            ->andWhere('p.dateFin >= :date')
            ->setParameter('actif', true)
            ->setParameter('date', $date)
            ->orderBy('p.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find promotions for a specific product at a given date
     *
     * @param Produit $produit
     * @param \DateTimeInterface|null $date
     * @return Promotion[]
     */
    public function findPromotionsForProduct(Produit $produit, ?\DateTimeInterface $date = null): array
    {
        $date = $date ?? new \DateTime();

        return $this->createQueryBuilder('p')
            ->innerJoin('p.produits', 'prod')
            ->andWhere('prod.id = :produitId')
            ->andWhere('p.actif = :actif')
            ->andWhere('p.dateDebut <= :date')
            ->andWhere('p.dateFin >= :date')
            ->setParameter('produitId', $produit->getId())
            ->setParameter('actif', true)
            ->setParameter('date', $date)
            ->orderBy('p.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find expired promotions
     *
     * @return Promotion[]
     */
    public function findExpired(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->andWhere('p.dateFin < :now')
            ->setParameter('now', $now)
            ->orderBy('p.dateFin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find upcoming promotions (not yet started)
     *
     * @return Promotion[]
     */
    public function findUpcoming(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('p')
            ->andWhere('p.dateDebut > :now')
            ->andWhere('p.actif = :actif')
            ->setParameter('now', $now)
            ->setParameter('actif', true)
            ->orderBy('p.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
