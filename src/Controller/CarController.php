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
        $startDateObj = null;
        $endDateObj = null;
       
        if ($start && $end) {
            try {
                $startDateObj = new \DateTimeImmutable($start);
                $endDateObj = new \DateTimeImmutable($end);
                if ($endDateObj < $startDateObj) {
                    $errors[] = 'End date must be after start date.';
                    $startDateObj = null;
                    $endDateObj = null;
                }
            } catch (\Exception $e) {
                $errors[] = 'Please provide valid dates.';
                $startDateObj = null;
                $endDateObj = null;
            }
        }

        $cars = $carRepository->search(
            $passengers > 0 ? $passengers : null,
            $startDateObj,
            $endDateObj
        );

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
