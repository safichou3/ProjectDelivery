<?php

namespace App\Controller;

use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceController extends AbstractController
{
    #[Route('/api/invoice/{orderId}', name: 'download_invoice', methods: ['GET'])]
    public function downloadInvoice(int $orderId, EntityManagerInterface $em): Response
    {
        $reservation = $em->getRepository(Reservation::class)->find($orderId);

        if (!$reservation) {
            throw $this->createNotFoundException('Order not found');
        }

        $user = $reservation->getClient();
        $unitPrice = $reservation->getDish() ? $reservation->getDish()->getPrice() : 0;
        $quantity = $reservation->getQuantity() ?? 1;
        $subTotal = $unitPrice * $quantity;
        $discountPercent = $subTotal > 0 ? round(($reservation->getDiscountAmount() / $subTotal) * 100, 2) : 0;
        $taxPercent = $subTotal - $reservation->getDiscountAmount() > 0 ? round(($reservation->getTaxAmount() / ($subTotal - $reservation->getDiscountAmount())) * 100, 2) : 0;

        $order = [
            'id' => $reservation->getId(),
            'customer' => $user ? $user->getName() : 'Unknown',
            'amount' => $reservation->getTotalAmount() ?? 0,
            'discountAmount' => $reservation->getDiscountAmount() ?? 0,
            'taxAmount' => $reservation->getTaxAmount() ?? 0,
            'date' => $reservation->getCreatedAt() ? $reservation->getCreatedAt()->format('Y-m-d') : '',
            'paymentType' => $reservation->getPaymentType() ?? '',
            'paymentStatus' => $reservation->getPaymentStatus() ?? '',
            'items' => [
                [
                    'dish' => $reservation->getDish() ? $reservation->getDish()->getName() : 'Unknown',
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'discountAmount' => $reservation->getDiscountAmount() ?? 0,
                    'discountPercent' => $discountPercent,
                    'taxAmount' => $reservation->getTaxAmount() ?? 0,
                    'taxPercent' => $taxPercent,
                    'total' => $reservation->getTotalAmount() ?? 0,
                ]
            ],
        ];

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $html = $this->renderView('invoice.html.twig', ['order' => $order]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice-'.$orderId.'.pdf"'
            ]
        );
    }

    #[Route('/api/invoice-multiple', name: 'download_multiple_invoice', methods: ['POST'])]
    public function downloadMultipleInvoice(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['orderIds']) || empty($data['orderIds'])) {
            throw $this->createNotFoundException('No order IDs provided');
        }

        $reservations = $em->getRepository(Reservation::class)->findBy(['id' => $data['orderIds']]);

        if (empty($reservations)) {
            throw $this->createNotFoundException('No orders found');
        }

        $firstReservation = $reservations[0];
        $user = $firstReservation->getClient();

        $allItems = [];
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($reservations as $reservation) {
            $unitPrice = $reservation->getDish() ? $reservation->getDish()->getPrice() : 0;
            $quantity = $reservation->getQuantity() ?? 1;
            $subTotal = $unitPrice * $quantity;
            $discountPercent = $subTotal > 0 ? round(($reservation->getDiscountAmount() / $subTotal) * 100, 2) : 0;
            $taxPercent = $subTotal - $reservation->getDiscountAmount() > 0 ? round(($reservation->getTaxAmount() / ($subTotal - $reservation->getDiscountAmount())) * 100, 2) : 0;

            $allItems[] = [
                'dish' => $reservation->getDish() ? $reservation->getDish()->getName() : 'Unknown',
                'quantity' => $quantity,
                'price' => $unitPrice,
                'discountAmount' => $reservation->getDiscountAmount() ?? 0,
                'discountPercent' => $discountPercent,
                'taxAmount' => $reservation->getTaxAmount() ?? 0,
                'taxPercent' => $taxPercent,
                'total' => $reservation->getTotalAmount() ?? 0,
            ];

            $totalAmount += $reservation->getTotalAmount() ?? 0;
            $totalDiscount += $reservation->getDiscountAmount() ?? 0;
            $totalTax += $reservation->getTaxAmount() ?? 0;
        }

        $order = [
            'id' => implode(',', array_map(fn($r) => $r->getId(), $reservations)),
            'customer' => $user ? $user->getName() : 'Unknown',
            'amount' => $totalAmount,
            'discountAmount' => $totalDiscount,
            'taxAmount' => $totalTax,
            'date' => $firstReservation->getCreatedAt() ? $firstReservation->getCreatedAt()->format('Y-m-d') : '',
            'paymentType' => $firstReservation->getPaymentType() ?? '',
            'paymentStatus' => $firstReservation->getPaymentStatus() ?? '',
            'items' => $allItems,
        ];

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $html = $this->renderView('invoice.html.twig', ['order' => $order]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $orderIds = implode('-', $data['orderIds']);
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoice-'.$orderIds.'.pdf"'
            ]
        );
    }
}
