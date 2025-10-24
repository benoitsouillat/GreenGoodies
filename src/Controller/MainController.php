<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class MainController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepo, TagAwareCacheInterface $cache): Response
    {
        // Mise en cache des produits de la page d'accueil
        $products = $cache->get('productsHome', function (ItemInterface $item) use ($productRepo) {
            $item->expiresAfter(3600);
            $item->tag('productsHomeCache');
            return $productRepo->findAllWithLimit(9);
        });
        return $this->render('main/index.html.twig', [
            'products' => $products,
            ]
        );
    }
}
