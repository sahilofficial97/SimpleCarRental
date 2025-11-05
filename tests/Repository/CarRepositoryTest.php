<?php

namespace App\Tests\Repository;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\CarRepository;
use App\Tests\Doctrine\DatabaseTestCase;

class CarRepositoryTest extends DatabaseTestCase
{
    public function testSearchAvailabilityAndSeatOrdering(): void
    {
        $em = $this->em;

        $user = (new User())
            ->setEmail('alice@example.com')
            ->setPassword('hash');

        $carExact = (new Car())
            ->setBrandName('ExactFive')
            ->setSeatAmount(5)
            ->setColor('black')
            ->setType('sedan')
            ->setPricePerDay('60.00');

        $carBigger = (new Car())
            ->setBrandName('SevenSeater')
            ->setSeatAmount(7)
            ->setColor('white')
            ->setType('minivan')
            ->setPricePerDay('80.00');

        $carExactButBooked = (new Car())
            ->setBrandName('FiveBooked')
            ->setSeatAmount(5)
            ->setColor('red')
            ->setType('sedan')
            ->setPricePerDay('55.00');

        $em->persist($user);
        $em->persist($carExact);
        $em->persist($carBigger);
        $em->persist($carExactButBooked);
        $em->flush();

        $reservation = (new Reservation())
            ->setUser($user)
            ->setCar($carExactButBooked)
            ->setStartDate(new \DateTimeImmutable('2025-01-10'))
            ->setEndDate(new \DateTimeImmutable('2025-01-15'));
        $em->persist($reservation);
        $em->flush();

        /** @var CarRepository $repo */
        $repo = $em->getRepository(Car::class);

        $start = new \DateTimeImmutable('2025-01-12');
        $end = new \DateTimeImmutable('2025-01-14');

        $results = $repo->search(5, $start, $end);

        // FiveBooked is unavailable, so results should include ExactFive (5 seats) and SevenSeater (7 seats)
        $this->assertCount(2, $results);
        $this->assertSame(5, $results[0]->getSeatAmount(), 'Exact seat match should come first');
        $this->assertSame(7, $results[1]->getSeatAmount(), 'Then the smallest larger seat count');
    }
}
