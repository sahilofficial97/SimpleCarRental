<?php

namespace App\EventSubscriber;

use App\Entity\Reservation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReservationCreatedSubscriber implements EventSubscriber
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.reservation')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Reservation) {
            return;
        }

        $user = $entity->getUser();
        $car = $entity->getCar();

        $this->logger->info('Reservation created', [
            'id' => $entity->getId(),
            'user_id' => method_exists($user, 'getId') ? $user->getId() : null,
            'user' => method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : null,
            'car_id' => $car?->getId(),
            'car' => method_exists($car, 'getBrandName') ? $car->getBrandName() : null,
            'start' => $entity->getStartDate()?->format('Y-m-d'),
            'end' => $entity->getEndDate()?->format('Y-m-d'),
            'total' => $entity->getTotalPrice(),
            'isPaid' => $entity->isPaid(),
            'paymentReference' => $entity->getPaymentReference(),
            'createdAt' => $entity->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ]);
    }
}
