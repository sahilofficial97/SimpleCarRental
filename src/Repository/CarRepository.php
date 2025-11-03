<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * @return Car[]
     */
    public function search(?int $minSeats = null): array
    {
        $qb = $this->createQueryBuilder('c');
        if ($minSeats !== null && $minSeats > 0) {
            $qb->andWhere('c.seatAmount >= :minSeats')
               ->setParameter('minSeats', $minSeats);
        }

        return $qb
            ->orderBy('c.type', 'ASC')
            ->addOrderBy('c.pricePerDay', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
