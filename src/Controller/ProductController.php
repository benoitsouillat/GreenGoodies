<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\OrderLineType;
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

    #[Route('/product/{id}', name: 'app_product_details', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function details(Request $request, Product $product, OrderService $orderService): Response
    {
        $quantityInCart = $orderService->getOrderLineQuantity($product) ?? 0;
        $form = $this->createForm(OrderLineType::class, $orderService->getOrderLine($product), []);
        if ($quantityInCart <= 0) {
            $form->remove('quantity');
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** Le produit n'existe pas dans le panier donc on le créé **/
            if ($form->getData()->getQuantity() === null) {
                $orderService->setOrderLineQuantity($product, 1);
                $this->addFlash('success', sprintf("Le produit %s a bien été ajouté à votre panier", $product->getName()));
                return $this->redirectToRoute('app_product_details', ['id' => $product->getId()]);
            }
            /** Le produit existe dans le panier, on le retire **/
            else if ($form->get('quantity')->getData() <= 0) {
                $orderService->removeOrderLine($product);
                $this->addFlash('success', sprintf("Le produit %s a bien été retiré de votre panier", $product->getName()));
                return $this->redirectToRoute('app_products');
            }
            /** Le produit existe dans le panier, on met à jour la quantité **/
            else {
                $orderService->setOrderLineQuantity($product, $form->get('quantity')->getData());
                $this->addFlash('success',"Le panier a bien été mis à jour");
                return $this->redirectToRoute('app_cart');
            }
        }
        return $this->render('product/details.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'quantityInCart' => $quantityInCart
        ]);
    }

}
