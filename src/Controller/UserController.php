<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('', name: 'app_user_')]
final class UserController extends AbstractController
{
    public function __construct(
        public readonly EntityManagerInterface $manager
    ) {}

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig', [
            'orders' => $this->manager->getRepository(Order::class)->findLastOrdersWithLimit($this->getUser(),5),
            'user' => $this->getUser()
        ]);
    }

    #[Route('/toggleApi', name: 'toggle_api_access' ,methods: ['GET'])]
    public function toggleApiAccess(): Response
    {
        $user = $this->getUser();
        $user->setApiAccess(!$user->isApiAccess());
        $this->manager->persist($user);
        $this->manager->flush();

        return $this->redirectToRoute('app_user_profile');
    }

    #[Route('/account-delete', name: 'delete_account')]
    public function deleteAccount(Security $security): Response
    {
        $user = $this->getUser();
        $orders = $this->manager->getRepository(Order::class)->findBy(['user' => $user]);
        foreach ($orders as $order) {
            $this->manager->remove($order);
        }
        $security->logout(false);
        $this->manager->remove($user);
        $this->manager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('app_home');
    }
}
