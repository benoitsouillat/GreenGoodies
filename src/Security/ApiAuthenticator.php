<?php

namespace App\Security;

use App\Exception\ApiException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class ApiAuthenticator extends JWTAuthenticator
{

    public function authenticate(Request $request): Passport
    {
        $passport = parent::authenticate($request);
        $user = $passport->getUser();
        if ($user && $user->isApiAccess() === false) {
            throw new ApiException('Votre accès à l\'API n\'est pas activé', 403);
        }

        return $passport;
    }

}
