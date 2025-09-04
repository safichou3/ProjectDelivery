<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(TokenStorageInterface $tokenStorage, SessionInterface $session): RedirectResponse
    {
//        $tokenStorage->setToken(null);
//        $session->invalidate();
//        $session->clear();
//
//        return $this->redirectToRoute('app_home');
        throw new \LogicException('This should never be reached!');
    }
}
