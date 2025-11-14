<?php

namespace App\Repository\Unite;

use App\Entity\Unite\ConversionStandard;
use App\Entity\Unite\Unite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConversionStandard>
 */
class ConversionStandardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversionStandard::class);
    }

    /**
     * @return ConversionStandard[]
     */
    public function findInvolvingUnit(Unite $unite): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.uniteOrigine = :unite')
            ->orWhere('c.uniteCible = :unite')
            ->setParameter('unite', $unite)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
