<?php

namespace App\Controller;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DiscountController extends AbstractController
{
    #[Route('/api/validate-discount', name: 'validate_discount', methods: ['POST'])]
    public function validateDiscount(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['discountCode'])) {
            return new JsonResponse(['error' => 'Discount code is required'], 400);
        }

        $discountCode = trim($data['discountCode']);
        $settings = $em->getRepository(Settings::class)->find(1);
        
        if (!$settings) {
            return new JsonResponse(['error' => 'Settings not found'], 404);
        }

        if (!$settings->isDiscountActive() || $settings->getDiscountCode() !== $discountCode) {
            return new JsonResponse([
                'valid' => false,
                'message' => 'Invalid or inactive discount code'
            ]);
        }

        return new JsonResponse([
            'valid' => true,
            'discountPercentage' => $settings->getDiscountPercentage(),
            'message' => 'Discount code applied successfully'
        ]);
    }
}
