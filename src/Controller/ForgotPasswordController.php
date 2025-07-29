<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, EntityManagerInterface $em, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $em->flush();

                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new Email())
                    ->from('no-reply@homemademeal.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html("<p>To reset your password, click the link: <a href='$resetUrl'>Reset Password</a></p>");

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Reset link sent. Please check your email.');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'Email not found.');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        if (!$user) {
            throw $this->createNotFoundException('Invalid token');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $em->flush();

            $this->addFlash('success', 'Password reset successfully.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }
}
