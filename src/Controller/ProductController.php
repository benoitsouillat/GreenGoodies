<?php

namespace App\Controller;

use App\Entity\Product;
use App\Services\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
    ) {}

    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function list(): Response
    {
        $products = $this->manager->getRepository(Product::class)->findAll();
        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[IsGranted('API_ACCESS')]
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function api_list(SerializerInterface $serializer,): JsonResponse
    {
        $products = $this->manager->getRepository(Product::class)->findAll();
        $json = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/product/{id}', name: 'app_product_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Product $product, OrderService $orderService): Response
    {
        return $this->render('product/details.html.twig', [
            'product' => $product,
            'quantityInCart' => $orderService->getOrderLineQuantity($product) ?? 0
        ]);
    }

    #[Route('/product/{id}/add', name: 'app_product_add_to_cart', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addToCart(Request $request, Product $product, OrderService $orderService): Response
    {
        $quantity = $orderService->getOrderLineQuantity($product);
        $orderLine = $orderService->setOrderLineQuantity($product, $request->request->get('quantity', 1));
        if ($quantity == 0 && $orderLine->getQuantity() == 1) {
            $this->addFlash('success', sprintf("Le produit %s a bien été ajouté à votre panier", $product->getName()));
            return $this->redirectToRoute('app_product_details', ['id' => $product->getId()]);
        }
        else {
            $this->addFlash('success',"Le panier a bien été mis à jour");
            return $this->redirectToRoute('app_user_basket');
        }
    }

}
