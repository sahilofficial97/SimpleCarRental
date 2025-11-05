<?php

namespace App\Tests\Repository;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use App\Tests\Doctrine\DatabaseTestCase;

class ReservationRepositoryTest extends DatabaseTestCase
{
    public function testCarHasOverlap(): void
    {
        $em = $this->em;

        $user = (new User())
            ->setEmail('john@example.com')
            ->setPassword('hash');

        $car = (new Car())
            ->setBrandName('Test Car')
            ->setSeatAmount(5)
            ->setColor('blue')
            ->setType('sedan')
            ->setPricePerDay('50.00');

        $em->persist($user);
        $em->persist($car);
        $em->flush();

        $existing = (new Reservation())
            ->setUser($user)
            ->setCar($car)
            ->setStartDate(new \DateTimeImmutable('2025-01-10'))
            ->setEndDate(new \DateTimeImmutable('2025-01-15'));
        $em->persist($existing);
        $em->flush();

        /** @var ReservationRepository $repo */
        $repo = $em->getRepository(Reservation::class);

        // Non-overlapping before (ends exactly at start) -> false
    $this->assertFalse($repo->carHasOverlap($car->getId(), new \DateTimeImmutable('2025-01-08'), new \DateTimeImmutable('2025-01-10')));

        // Non-overlapping after (starts exactly at end) -> false
    $this->assertFalse($repo->carHasOverlap($car->getId(), new \DateTimeImmutable('2025-01-15'), new \DateTimeImmutable('2025-01-18')));

        // Overlapping inside -> true
    $this->assertTrue($repo->carHasOverlap($car->getId(), new \DateTimeImmutable('2025-01-12'), new \DateTimeImmutable('2025-01-13')));

        // Overlapping spanning -> true
    $this->assertTrue($repo->carHasOverlap($car->getId(), new \DateTimeImmutable('2025-01-09'), new \DateTimeImmutable('2025-01-16')));
    }
}
