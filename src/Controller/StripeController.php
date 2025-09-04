<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservation;

class StripeController
{
    #[Route('/api/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $totalAmount = $data['amount'] ?? 0;
        $orderIds = $data['orderIds'] ?? [];
        $stripeSecret = $_ENV('STRIPE_SECRET');
        Stripe::setApiKey($stripeSecret);
        $origin = $_ENV['APP_URL'];

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Order Payment',
                    ],
                    'unit_amount' => $totalAmount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $origin . '/thank-you?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $origin . '/payment',
            'metadata' => [
                'order_ids' => implode(',', $orderIds),
                'total_amount' => $totalAmount
            ],
        ]);

        return new JsonResponse(['url' => $session->url]);
    }

    #[Route('/api/stripe-webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? 'whsec_test_secret';

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            if ($_ENV['APP_ENV'] === 'dev') {
                $event = json_decode($payload, true);
                if (!$event || !isset($event['type'])) {
                    return new JsonResponse(['error' => 'Invalid payload'], 400);
                }
            } else {
                return new JsonResponse(['error' => 'Invalid signature'], 400);
            }
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderIds = $session->metadata->order_ids ?? '';
            
            if ($orderIds) {
                $orderIdArray = explode(',', $orderIds);
                
                foreach ($orderIdArray as $orderId) {
                    $reservation = $em->getRepository(Reservation::class)->find($orderId);
                    if ($reservation) {
                        $reservation->setPaymentStatus('paid');
                        $em->persist($reservation);
                    }
                }
                
                $em->flush();
            }
        }

        return new JsonResponse(['status' => 'success']);
    }
}
