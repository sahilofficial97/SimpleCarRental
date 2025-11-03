<?php

namespace App\Controller;

use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarController extends AbstractController
{
    #[Route('/cars', name: 'app_cars_search')]
    public function search(Request $request, CarRepository $carRepository): Response
    {
        $passengers = $request->query->getInt('passengers', 0);
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $errors = [];
        // Basic date sanity check (availability logic can be wired later when bookings exist)
        if ($start && $end) {
            try {
                $startDate = new \DateTimeImmutable($start);
                $endDate = new \DateTimeImmutable($end);
                if ($endDate < $startDate) {
                    $errors[] = 'End date must be after start date.';
                }
            } catch (\Exception $e) {
                $errors[] = 'Please provide valid dates.';
            }
        }

        $cars = $carRepository->search($passengers > 0 ? $passengers : null);

        return $this->render('car/search.html.twig', [
            'cars' => $cars,
            'filters' => [
                'passengers' => $passengers ?: '',
                'start' => $start ?: '',
                'end' => $end ?: '',
            ],
            'errors' => $errors,
        ]);
    }
}
