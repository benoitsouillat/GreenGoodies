<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserHasApiAccessVoter extends Voter
{
    public const ACCESS = 'API_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /* Verify if user has ApiAccess in Entity Method */
        $user = $token->getUser();
        return $user instanceof User ? $user->isApiAccess() : false;
    }
}
