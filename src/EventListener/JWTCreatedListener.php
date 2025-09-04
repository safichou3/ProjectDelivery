<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $payload = $event->getData();

        if ($user instanceof User) {
            $payload['id']          = $user->getId();
            $payload['name']        = $user->getName();
            $payload['email']       = $user->getEmail();
            $payload['phone']       = $user->getPhoneNumber();
            $payload['country']     = $user->getCountry();
            $payload['city']        = $user->getCity();
            $payload['postalCode']  = $user->getPostalCode();
            $payload['address']     = $user->getAddress();
            $payload['role']        = $user->getRole();
            $payload['roles']       = $user->getRoles();
            $payload['isVerified']  = $user->isVerified();
            $payload['createdAt']   = $user->getCreatedAt()?->format('Y-m-d H:i:s');
            $payload['updatedAt']   = $user->getUpdatedAt()?->format('Y-m-d H:i:s');
        }

        $event->setData($payload);
    }
}
