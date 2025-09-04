<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password_request', methods: ['GET', 'POST'])]
    public function request(Request $request, MailerInterface $mailer, EntityManagerInterface $em, TokenGeneratorInterface $tokenGenerator, ValidatorInterface $validator): Response {
        if ($request->isMethod('POST')) {
            $content = $request->getContent();
            $data = json_decode($content, true);
            
            if (!$data) {
                $data = $request->request->all();
            }
            
            $email = $data['email'] ?? '';

            $emailConstraint = new Assert\Email(['message' => 'Please enter a valid email address']);
            $notBlankConstraint = new Assert\NotBlank(['message' => 'Email is required']);
            
            $violations = $validator->validate($email, [$notBlankConstraint, $emailConstraint]);
            
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                
                if ($request->isXmlHttpRequest() || $request->getContentType() === 'json') {
                    return new JsonResponse([
                        'success' => false,
                        'message' => implode(', ', $errors)
                    ], 400);
                }
                
                $this->addFlash('danger', implode(', ', $errors));
                return $this->redirectToRoute('app_forgot_password_request');
            }
            
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+24 hours'));
                $em->flush();

                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new Email())
                    ->from('info@veteranlogix.com')
                    ->replyTo('support@chefcheztoi.fr')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request - Chefcheztoi')
                    ->html($this->renderView('emails/password_reset.html.twig', [
                        'user' => $user,
                        'resetUrl' => $resetUrl
                    ]));

                try {
                    $mailer->send($emailMessage);
                    if ($request->isXmlHttpRequest() || $request->getContentType() === 'json') {
                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Password reset link has been sent to your email. Please check your inbox.'
                        ]);
                    }
                    $this->addFlash('success', 'Password reset link has been sent to your email. Please check your inbox.');
                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    error_log("Email sending failed: " . $e->getMessage());
                    error_log("Email details - To: " . $user->getEmail() . ", From: frank@experiencedynamics.com");
                    
                    if ($request->isXmlHttpRequest() || $request->getContentType() === 'json') {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Failed to send email. Please try again later. Error: ' . $e->getMessage()
                        ], 500);
                    }
                    
                    $this->addFlash('danger', 'Failed to send email. Please try again later.');
                    return $this->redirectToRoute('app_forgot_password_request');
                }
            } else {
                if ($request->isXmlHttpRequest() || $request->getContentType() === 'json') {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'No account found with this email address.'
                    ], 404);
                }
                
                $this->addFlash('danger', 'No account found with this email address.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || ($user->getResetTokenExpiresAt() && $user->getResetTokenExpiresAt() < new \DateTime())) {
            if ($user) {
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                $em->flush();
            }

            $message = !$user ? 'Invalid reset token.' : 'Your reset token has expired. Please request a new one.';
            $this->addFlash('danger', $message);
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if (empty($password)) {
                $this->addFlash('danger', 'Password is required');
            } elseif (strlen($password) < 6) {
                $this->addFlash('danger', 'Password must be at least 6 characters long');
            } elseif ($password !== $confirmPassword) {
                $this->addFlash('danger', 'Passwords do not match');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                $em->flush();

                $this->addFlash('success', 'Your password has been reset successfully. You can now login with your new password.');
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }
}
