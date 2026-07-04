<?php

namespace App\Repository\Stock;

use App\Entity\Produit\Produit;
use App\Entity\Stock\MouvementStock;
use App\Enum\TypeMouvement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MouvementStock>
 */
class MouvementStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementStock::class);
    }

    /**
     * Récupère l'historique des mouvements d'un produit
     *
     * @return MouvementStock[]
     */
    public function findByProduit(Produit $produit, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.produit = :produit')
            ->setParameter('produit', $produit)
            ->orderBy('m.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les mouvements sur une période
     *
     * @return MouvementStock[]
     */
    public function findByPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.createdAt BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les mouvements par type
     *
     * @return MouvementStock[]
     */
    public function findByType(TypeMouvement $type, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->setParameter('type', $type)
            ->orderBy('m.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Calcule le stock actuel d'un produit basé sur les mouvements
     */
    public function getStockActuel(Produit $produit): float
    {
        $dernierMouvement = $this->createQueryBuilder('m')
            ->andWhere('m.produit = :produit')
            ->setParameter('produit', $produit)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($dernierMouvement) {
            return $dernierMouvement->getStockApres();
        }

        // Si aucun mouvement, retourner le stock initial
        return $produit->getStockInitial();
    }

    /**
     * Récupère les mouvements récents
     *
     * @return MouvementStock[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de mouvements par type pour un produit
     */
    public function countByTypeForProduit(Produit $produit, TypeMouvement $type): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.produit = :produit')
            ->andWhere('m.type = :type')
            ->setParameter('produit', $produit)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
