<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function carHasOverlap(int $carId, \DateTimeImmutable $start, \DateTimeImmutable $end): bool
    {
        // Overlap if: existing.start < requestedEnd AND existing.end > requestedStart
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.car = :carId')
            ->andWhere('r.startDate < :end')
            ->andWhere('r.endDate > :start')
            ->setParameter('carId', $carId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count > 0;
    }
}
