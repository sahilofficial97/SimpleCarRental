<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Car;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_reservations')]
    public function index(ReservationRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $reservations = $repository->findBy(['user' => $user], ['startDate' => 'DESC']);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/cars/{id}/reserve', name: 'app_reserve_car', methods: ['POST'])]
    public function reserve(
        int $id,
        Request $request,
        ReservationRepository $reservations,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrf
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('reserve_car_' . $id, $token)) {
            $this->addFlash('error', 'Invalid request, please try again.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        $startStr = (string) $request->request->get('start', '');
        $endStr = (string) $request->request->get('end', '');
        if ($startStr === '' || $endStr === '') {
            $this->addFlash('error', 'Please select start and end dates.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        try {
            $start = new \DateTimeImmutable($startStr);
            $end = new \DateTimeImmutable($endStr);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Invalid dates provided.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        if ($end <= $start) {
            $this->addFlash('error', 'End date must be after start date.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        // Load the car reference
        /** @var Car|null $car */
        $car = $em->getRepository(Car::class)->find($id);
        if (!$car) {
            $this->addFlash('error', 'Car not found.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        // Check availability
        if ($reservations->carHasOverlap($id, $start, $end)) {
            $this->addFlash('error', 'Sorry, this car is not available for the selected dates.');
            return $this->redirectToRoute('app_cars_search', $request->query->all());
        }

        // Create reservation
        $reservation = (new Reservation())
            ->setUser($this->getUser())
            ->setCar($car)
            ->setStartDate($start)
            ->setEndDate($end);

        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Your reservation is confirmed.');
        return $this->redirectToRoute('app_reservations');
    }
}
