<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginAuthenticator;


class ApiSessionController extends AbstractController
{
    #[Route('/api/session-login', name: 'api_session_login', methods: ['POST'])]
    public function sessionLogin(
        Request $request,
        UserProviderInterface $userProvider,
        UserAuthenticatorInterface $authenticator,
        LoginAuthenticator $loginAuthenticator
    ): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return new JsonResponse(['error' => 'JWT Token not found'], 401);
        }

        $jwtToken = str_replace('Bearer ', '', $authHeader);
        $payload = json_decode(base64_decode(explode('.', $jwtToken)[1]), true);
        $email = $payload['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Invalid token'], 400);
        }

        $user = $userProvider->loadUserByIdentifier($email);

        $authenticator->authenticateUser($user, $loginAuthenticator, $request);

        $roles = $user->getRoles();
        $redirect = null;
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $redirect = $this->generateUrl('admin_dashboard');
        } elseif (in_array('ROLE_CHEF', $roles, true)) {
            $redirect = $this->generateUrl('chef_dashboard');
        } elseif (in_array('ROLE_USER', $roles, true)) {
            $redirect = $this->generateUrl('app_home');
        }

        return new JsonResponse([
            'success' => true,
            'user' => [
                'email' => $user->getUserIdentifier(),
                'roles' => $roles,
            ],
            'redirect' => $redirect,
        ]);
    }
}
