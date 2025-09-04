<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\OtpVerificationFormType;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Service\TwilioService;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegistrationController extends AbstractController
{
    #[Route('/api/verify-otp', name: 'api_verify_otp', methods: ['POST'])]
    public function apiVerifyOtp(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $otp = $data['otp'] ?? null;
        $pendingUser = $session->get('pending_user');
        if (!$pendingUser) {
            return $this->json(['error' => 'No pending user found'], 400);
        }
        if ($otp === $pendingUser['otp'] && new \DateTime() < new \DateTime($pendingUser['otp_expires'])) {
            $user = new User();
            $user->setName($pendingUser['name']);
            $user->setEmail($pendingUser['email']);
            $user->setPassword($pendingUser['password']);
            $user->setPhoneNumber($pendingUser['phone']);
            $user->setAddress($pendingUser['address'] ?? null);
            $user->setIsVerified(true);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setRole($pendingUser['role'] ?? 'user');

            $entityManager->persist($user);
            $entityManager->flush();

            $session->remove('pending_user');
            $token = $jwtManager->create($user);

            $userAuthenticator->authenticateUser($user, $authenticator, $request);

            return $this->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'token'   => $token,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                ]
            ]);
        }

        return $this->json(['error' => 'Invalid or expired OTP'], 400);
    }


    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function apiRegister(Request $request, UserPasswordHasherInterface $passwordHasher, TwilioService $twilioService, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];

        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $phone = trim((string)($data['phone'] ?? ''));
        $address = trim((string)($data['address'] ?? ''));
        $role = trim((string)($data['role'] ?? ''));

        if ($name === '' || $email === '' || $password === '' || $phone === '') {
            return $this->json(['error' => 'All fields are required'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json(['error' => 'Email already exists'], 400);
        }

        $otp = (string)random_int(100000, 999999);
        $expiresAt = (new \DateTime())->modify('+5 minutes');
        $request->getSession()->set('pending_user', [
            'name' => $name,
            'email' => $email,
            'password' => $passwordHasher->hashPassword(new User(), $password),
            'phone' => $phone,
            'role' => $role,
            'address' => $address ?: null,
            'otp' => $otp,
            'otp_expires' => $expiresAt->format('Y-m-d H:i:s')
        ]);

//         $twilioService->sendOtp($pendingUser['phone'], $otp);

        error_log("API OTP for testing: $otp");

        return $this->json([
            'success' => true,
            'message' => 'OTP generated successfully. Verify to complete registration.',
            'otp_for_testing' => $otp,
            'user' => [
                'email' => $email,
                'name' => $name,
                'phone' => $phone
            ]
        ], 201);
    }

    #[Route('/api/resend-otp', name: 'api_resend_otp', methods: ['POST'])]
    public function apiResendOtp(Request $request, SessionInterface $session, TwilioService $twilioService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = trim($data['email'] ?? '');

        if (!$email) {
            return $this->json(['error' => 'Email is required'], 400);
        }

        $pendingUser = $session->get('pending_user');

        if (!$pendingUser || $pendingUser['email'] !== $email) {
            return $this->json(['error' => 'No pending registration found for this email'], 400);
        }

        $otp = (string)random_int(100000, 999999);
        $expiresAt = (new \DateTime())->modify('+5 minutes');

        $pendingUser['otp'] = $otp;
        $pendingUser['otp_expires'] = $expiresAt->format('Y-m-d H:i:s');
        $session->set('pending_user', $pendingUser);

        // $twilioService->sendOtp($pendingUser['phone'], $otp);

        error_log("Resent OTP: $otp");

        return $this->json([
            'success' => true,
            'message' => 'OTP resent successfully',
            'otp_for_testing' => $otp
        ]);
    }

}