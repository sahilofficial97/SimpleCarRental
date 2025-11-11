<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Service\Payment\SentooClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class PaymentController extends AbstractController
{
    #[Route('/payments/create', name: 'app_payment_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SentooClient $sentoo,
        ReservationRepository $reservations,
        LoggerInterface $logger
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $token = (string) $request->request->get('_token', '');
        $carIdForToken = (int) $request->request->get('car_id', 0);
        if (!$this->isCsrfTokenValid('payment_create_' . $carIdForToken, $token)) {
            $this->addFlash('error', 'Invalid request, please try again.');
            return $this->redirectToRoute('app_cars_search');
        }

        // Expect fields: car_id, start, end, description(optional)
        $carId = (int) $request->request->get('car_id', 0);
        $startStr = (string) $request->request->get('start', '');
        $endStr = (string) $request->request->get('end', '');
        $description = (string) ($request->request->get('description', 'Car rental payment'));

        if ($carId <= 0 || $startStr === '' || $endStr === '') {
            $this->addFlash('error', 'Missing required payment information.');
            return $this->redirectToRoute('app_cars_search');
        }

        try {
            $start = new \DateTimeImmutable($startStr);
            $end = new \DateTimeImmutable($endStr);
        } catch (\Exception) {
            $this->addFlash('error', 'Invalid dates provided.');
            return $this->redirectToRoute('app_cars_search');
        }

        if ($end <= $start) {
            $this->addFlash('error', 'End date must be after start date.');
            return $this->redirectToRoute('app_cars_search');
        }

        /** @var Car|null $car */
        $car = $em->getRepository(Car::class)->find($carId);
        if (!$car) {
            $this->addFlash('error', 'Car not found.');
            return $this->redirectToRoute('app_cars_search');
        }

        // Ensure dates are available for this car
        if ($reservations->carHasOverlap($carId, $start, $end)) {
            $this->addFlash('error', 'Sorry, this car is not available for the selected dates.');
            return $this->redirectToRoute('app_cars_search', [
                'passengers' => $request->request->get('passengers', ''),
                'start' => $startStr,
                'end' => $endStr,
            ]);
        }

        // Create a pending reservation (isPaid = false)
        $reservation = (new Reservation())
            ->setUser($this->getUser())
            ->setCar($car)
            ->setStartDate($start)
            ->setEndDate($end)
            ->setIsPaid(false);
        $em->persist($reservation);
        $em->flush(); // triggers price calculation and assigns id

        // Compute amount from persisted reservation totalPrice in minor units (e.g., cents)
        $amountMinor = (int) round(((float) $reservation->getTotalPrice()) * 100);

        $expires = (new \DateTimeImmutable('now'))->modify('+1 day');

        // Include reservation id in return payload so we can mark it paid later
        $payload = http_build_query([
            'reservation_id' => $reservation->getId(),
            'car' => $carId,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);

        $result = $sentoo->createPayment($amountMinor, $description, $expires, $payload);

        // Debug log: parsed URL and raw payload
        $logger->info('Sentoo createPayment parsed URL', ['url' => $result['url'] ?? null]);
        if (is_array($result['raw'] ?? null)) {
            $logger->info('Sentoo createPayment raw JSON', ['raw' => $result['raw']]);
        } else {
            $logger->info('Sentoo createPayment raw text', ['raw' => (string) ($result['raw'] ?? '')]);
        }

        // Save provider payment id (under success.message) if provided in response
        if (is_array($result['raw'] ?? null)) {
            $paymentId = $result['raw']['success']['message'] ?? null;
            if (is_string($paymentId) && $paymentId !== '') {
                $reservation->setPaymentReference($paymentId);
                $em->flush();
            }
        }

        // Determine redirect URL (prefer success.data.url)
        $redirectUrl = $result['url'] ?? null;
        if (!$redirectUrl && is_array($result['raw'] ?? null)) {
            $redirectUrl = $result['raw']['success']['data']['url'] ?? null;
        }

        $isAjax = $request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json');

        if ($redirectUrl && is_string($redirectUrl) && $redirectUrl !== '') {
            $logger->info('Redirecting user to payment page', ['url' => $redirectUrl, 'ajax' => $isAjax]);
            if ($isAjax) {
                return new JsonResponse([
                    'status' => 'ok',
                    'payment_url' => $redirectUrl,
                    'reservation_id' => $reservation->getId(),
                    'payment_reference' => $reservation->getPaymentReference(),
                ]);
            }
            return $this->redirect($redirectUrl);
        }

        // Log and handle missing URL
        $logger->error('No valid payment URL found in response', [
            'parsed_url' => $result['url'] ?? null,
            'raw_response' => $result['raw'] ?? null,
            'ajax' => $isAjax,
        ]);
        if ($isAjax) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Payment created but no redirect URL found',
                'raw' => $result['raw'] ?? null,
            ], 502);
        }

        // If payment creation fails, roll back the pending reservation to avoid blocking the car
        $em->remove($reservation);
        $em->flush();
        $this->addFlash('error', 'Could not start payment. Please try again later.');
        return $this->redirectToRoute('app_cars_search', [
            'passengers' => $request->request->get('passengers', ''),
            'start' => $startStr,
            'end' => $endStr,
        ]);
    }
}
