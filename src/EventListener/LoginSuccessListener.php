<?php

namespace App\EventListener;

use App\Entity\User;
use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $firewallName = $event->getFirewallName();
        if ($firewallName !== 'api') {
            return;
        }
        $user = $event->getUser();
        if (!$user instanceof User)
        {
            throw new ApiException('Une erreur s\'est produite', 500);
        }

        if ($user->isApiAccess() === false)
        {
            throw new ApiException('Votre accès à l\'API n\'est pas activé', 403);
        }

    }
}
