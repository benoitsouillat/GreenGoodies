<?php

namespace App\Security;

use App\Exception\ApiException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAuthenticator extends JWTAuthenticator
{
    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcher,
        TokenExtractorInterface $tokenExtractor,
        UserProviderInterface $userProvider,
        ?TranslatorInterface $translator = null,
        ?Security $security = null,
    ) {
        parent::__construct($jwtManager, $eventDispatcher, $tokenExtractor, $userProvider, $translator);
        $this->security = $security;
    }

    public function authenticate(Request $request): Passport
    {

        $passport = parent::authenticate($request);
        $user = $passport->getUser();
        if ($this->security->isGranted('API_ACCESS', $user) === false) {
            throw new ApiException('Votre accès à l\'API n\'est pas activé', 403);
        }

        return $passport;
    }

}
