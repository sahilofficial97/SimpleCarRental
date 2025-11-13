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
     * 
     * @return Car[]
     */
    public function search(?int $minSeats = null, ?\DateTimeImmutable $start = null, ?\DateTimeImmutable $end = null): array
    {
        $qb = $this->createQueryBuilder('c');

          if ($minSeats !== null && $minSeats > 0) {
                $qb->andWhere('c.seatAmount >= :minSeats')
                    ->setParameter('minSeats', $minSeats);

                // Order exact matches first, then the smallest fitting cars, then by price
                $qb->addSelect('(CASE WHEN c.seatAmount = :reqSeats THEN 0 ELSE 1 END) AS HIDDEN seatsExactFirst')
                    ->setParameter('reqSeats', $minSeats)
                    ->addOrderBy('seatsExactFirst', 'ASC')
                    ->addOrderBy('c.seatAmount', 'ASC')
                    ->addOrderBy('c.pricePerDay', 'ASC');
          }

        // When both dates are provided, exclude cars that have overlapping reservations.
        if ($start !== null && $end !== null) {
            $qb->leftJoin('App\\Entity\\Reservation', 'r', 'WITH', 'r.car = c AND r.startDate < :end AND r.endDate > :start')
               ->andWhere('r.id IS NULL')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        // Default ordering when no seat filter was provided
        if ($minSeats === null || $minSeats <= 0) {
            $qb->orderBy('c.seatAmount', 'ASC')
               ->addOrderBy('c.pricePerDay', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }
}
