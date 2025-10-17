<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\OrderLineType;
use App\Services\OrderService;
use App\Services\ProductService;
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

    #[Route('/product/{id}', name: 'app_product_details', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function details(Request $request, Product $product, OrderService $orderService, ProductService $productService): Response
    {
        $quantityInCart = $orderService->getOrderLineQuantity($product) ?? 0;
        $form = $this->createForm(OrderLineType::class, $orderService->getOrderLine($product), []);
        if ($quantityInCart <= 0) {
            $form->remove('quantity');
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $retour = $productService->manageQuantity($product, $form->getData()->getQuantity());
            $this->addFlash('success', $retour['message']);
            return  $this->redirectToRoute($retour['route'], $retour['params']);
        }
        return $this->render('product/details.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'quantityInCart' => $quantityInCart
        ]);
    }

}
