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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly TagAwareCacheInterface $cache,
        private readonly ProductService $productService,

    ) {}

    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function list(): Response
    {
        // Mise en cache de la page tous les produits pour une durée d'une heure
        $idCache = "getAllProducts";
        $products = $this->cache->get($idCache, function (ItemInterface $item) use ($idCache) {
            $item->expiresAfter(3600);
            $item->tag('productsCache');
            return $this->manager->getRepository(Product::class)->findAll();
        });

        return $this->render('product/list.html.twig', [
            'products' => $products,
        ]);
    }

    #[IsGranted('API_ACCESS')]
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function api_list(SerializerInterface $serializer): JsonResponse
    {
        // Mise en cache de la page tous les produits pour une durée d'une heure
        $idCache = "getAllProducts";
        $products = $this->cache->get($idCache, function (ItemInterface $item) use ($idCache) {
            $item->expiresAfter(3600);
            $item->tag('productsCache');
            return $this->manager->getRepository(Product::class)->findAll();
        });

        // Si aucun produit n'est trouvé, on retourne un code 204
        if (empty($products)) {
            return new JsonResponse("", Response::HTTP_NO_CONTENT);
        }

        // Préparation des produits pour l'API (mise à jour des URL des images)
        $products = $this->productService->prepareProductForApi($products);

        // Sérialisation des produits en JSON
        $json = $serializer->serialize($products, 'json', ['groups' => 'getProducts']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/product/{id}', name: 'app_product_details', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function details(Request $request, Product $product, OrderService $orderService, ProductService $productService): Response
    {
        // On récupère la quantité de ce produit dans le panier
        $quantityInCart = $orderService->getOrderLineQuantity($product) ?? 0;
        $form = $this->createForm(OrderLineType::class, $orderService->getOrderLine($product), []);

        // Si l'article n'est pas dans le panier, on retire le champ quantity du formulaire pour mettre le bouton "Ajouter au panier"
        if ($quantityInCart <= 0) {
            $form->remove('quantity');
        }
        // On récupère la valeur postée pour enregistrer la bonne quantité dans le panier
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On met à jour la quantité de ce produit dans le panier via le service ProductService
            $retour = $productService->manageQuantity($product, $form->getData()->getQuantity());
            $this->addFlash('success', $retour['message']);

            return $this->redirectToRoute($retour['route'], $retour['params']);
        }

        // On crée un cache pour ce produit pour une durée d'une heure
        $idCache = "getProduct-" . $product->getId();
        $product = $this->cache->get($idCache, function (ItemInterface $item) use ($product) {
            $item->expiresAfter(3600);
            $item->tag('productCache' . $product->getId());

            return $this->manager->getRepository(Product::class)->find($product);
        });

        return $this->render('product/details.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'quantityInCart' => $quantityInCart
        ]);
    }
}
