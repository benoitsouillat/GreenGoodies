<?php

namespace App\Controller;

use App\Form\UserRegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function index(): Response
    {
        return $this->render('user/profilePage.html.twig');
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request,UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UserRegisterType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if (!$form->get('CGU')->getData()) {
                $this->addFlash('danger', 'Vous devez accepter les conditions générales d\'utilisation.');
            }
            if (!($form->get('confirmPassword')->getData() == $form->get('plainPassword')->getData())) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                return $this->render('user/registerPage.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $user->setPassword(
                $hasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setRoles(['ROLE_USER']);
            $this->manager->persist($user);
            $this->manager->flush();
            $this->addFlash('success', 'Votre compte a bien été créé.');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/registerPage.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
