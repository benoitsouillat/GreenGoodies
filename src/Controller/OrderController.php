<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderLineType;
use App\Services\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name: 'app_')]
final class OrderController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function cart(OrderService $orderService): Response
    {
        $currentOrder = $orderService->getCurrentOrder();
        $form = $this->createForm(OrderLineType::class);

        return $this->render('user/cart.html.twig', [
            'user' => $this->getUser(),
            'order' => $currentOrder,
        ]);
    }

    #[Route('/cart/clear', name: 'cart_reset')]
    public function clearCart(OrderService $orderService): Response
    {
        $orderService->resetOrder();
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/order/validate', name: 'order_validate')]
    public function validateCart(OrderService $orderService): Response
    {
        $orderService->validateOrder() ?
            $this->addFlash('success', 'Votre commande a été validée avec succès.') :
            $this->addFlash('warning', 'Votre panier est vide.');

        return $this->redirectToRoute('app_user_profile');
    }

    #[Route('/order/details', name: 'order_details', requirements: ['id' => '\d+'])]
    public function orderDetails(Order $order): Response
    {
        return $this->render('user/order.html.twig', [
            'order' => $order,
        ]);
    }
}
