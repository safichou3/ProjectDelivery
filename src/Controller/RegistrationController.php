<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
//use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Service\TwilioService;

class RegistrationController extends AbstractController
{
//    private EmailVerifier $emailVerifier;

//    public function __construct(EmailVerifier $emailVerifier)
//    {
//        $this->emailVerifier = $emailVerifier;
//    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, TwilioService $twilioService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $otp = random_int(100000, 999999);
            $user->setPhoneOtp($otp);
            $user->setOtpExpiresAt((new \DateTime())->modify('+5 minutes'));

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $user->setIsVerified(true);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $twilioService->sendSms($user->getPhoneNumber(), "Your OTP is: $otp");

            // generate a signed url and email it to the user
//            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
//                (new TemplatedEmail())
//                    ->from(new Address('noreply@homemademeals.com', 'Homemade Meals'))
//                    ->to($user->getEmail())
//                    ->subject('Please Confirm your Email')
//                    ->htmlTemplate('registration/confirmation_email.html.twig')
//            );
            // do anything else you need here, like send an email

//            return $userAuthenticator->authenticateUser(
//                $user,
//                $authenticator,
//                $request
//            );

            $this->addFlash('success', 'An OTP has been sent to your phone. Please verify.');
            return $this->redirectToRoute('app_verify_otp', ['id' => $user->getId()]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/verify-otp/{id}', name: 'app_verify_otp')]
    public function verifyOtp(Request $request, User $user, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator): Response {
        $form = $this->createForm(OtpVerificationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $enteredOtp = $form->get('otp')->getData();

            if (
                $user->getPhoneOtp() === $enteredOtp &&
                $user->getOtpExpiresAt() > new \DateTime()
            ) {
                // Clear OTP
                $user->setPhoneOtp(null);
                $user->setOtpExpiresAt(null);
                $user->setIsVerified(true);
                $entityManager->flush();

                $this->addFlash('success', 'Phone verified successfully.');

                // Auto-login after OTP verification
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }

            $this->addFlash('error', 'Invalid or expired OTP. Please try again.');
        }

        return $this->render('registration/verify_otp.html.twig', [
            'otpForm' => $form->createView(),
        ]);
    }

}
