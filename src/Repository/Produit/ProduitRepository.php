<?php

namespace App\Repository\Produit;

use App\Entity\Produit\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * @return Produit[] Returns an array of Produit objects
     */
    public function searchByNameOrCode(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.nom) LIKE LOWER(:query) OR LOWER(p.code) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
