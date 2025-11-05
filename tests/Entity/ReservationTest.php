<?php

namespace App\Tests\Entity;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use App\Tests\Doctrine\DatabaseTestCase;

class ReservationTest extends DatabaseTestCase
{
    public function testTotalPriceCalculatedOnPersist(): void
    {
        $em = $this->em;

        $user = (new User())
            ->setEmail('bob@example.com')
            ->setPassword('hash');

        $car = (new Car())
            ->setBrandName('PricedCar')
            ->setSeatAmount(4)
            ->setColor('silver')
            ->setType('sedan')
            ->setPricePerDay('49.99');

        $em->persist($user);
        $em->persist($car);
        $em->flush();

        $reservation = (new Reservation())
            ->setUser($user)
            ->setCar($car)
            ->setStartDate(new \DateTimeImmutable('2025-02-01'))
            ->setEndDate(new \DateTimeImmutable('2025-02-04')); // 3 days

        $em->persist($reservation);
        $em->flush();

        // 49.99 * 3 = 149.97
        $this->assertSame('149.97', $reservation->getTotalPrice());
    }
}
