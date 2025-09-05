<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('', name: 'app_user_')]
final class UserController extends AbstractController
{

    public function __construct(
        public readonly EntityManagerInterface $manager
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }
}
