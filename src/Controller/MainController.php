<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepo): Response
    {
        $products = $productRepo->findAllWithLimit($limit = 9);
        return $this->render('main/index.html.twig', [
            'products' => $products,
            ]
        );
    }
}
