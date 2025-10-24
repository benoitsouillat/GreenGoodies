<?php

namespace App\Services;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class ProductService
{
    public function __construct(
        private OrderService $orderService,
        private RequestStack $requestStack
    )
    {}

    /**
     * Retourne un tableau avec le message, la route et les paramètres de route en fonction de la quantité du formulaire Quantity
     * @param int|null $quantity
     * @param Product $product
     * @return array
     */
    public function manageQuantity(Product $product, ?int $quantity = null): array
    {
        $values = [
            'message' => '',
            'route' => 'app_product_details',
            'params' => [],
        ];
        /** Le produit n'existe pas dans le panier donc on le crée **/
        if ($quantity === null) {
            $this->orderService->setOrderLineQuantity($product, 1);
            $values['message'] =  sprintf("Le produit %s a bien été ajouté à votre panier", $product->getName());
            $values['params'] = ['id' => $product->getId()];
        }
        /** Le produit existe dans le panier, on le retire **/
        else if ($quantity <= 0) {
            $this->orderService->removeOrderLine($product);
            $values['message'] = sprintf("Le produit %s a bien été retiré de votre panier", $product->getName());
            $values['route'] = 'app_products';
        }
        /** Le produit existe dans le panier, on met à jour la quantité **/
        else {
            $this->orderService->setOrderLineQuantity($product, $quantity);

            $values['message'] = sprintf("La quantité du produit %s a bien été mise à jour dans votre panier", $product->getName());
            $values['route'] = 'app_cart';
        }
        return $values;
    }

    /**
     * Retourne l'URL complète de l'image du produit pour l'API
     * @param Product $product
     * @return string
     */
    public function setApiPictureURL(Product $product): string
    {
        $imagePath = $product->getPicture();
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request->getSchemeAndHttpHost();

        return $baseUrl . '/images/products/' . $imagePath;
    }

    /**
     * Prépare les produits pour l'API en mettant à jour l'URL de l'image
     * @param array $products
     * @return array(Product)
     * */
    public function prepareProductForApi(array $products): array
    {
        foreach ($products as $product) {
            $product->setPicture($this->setApiPictureURL($product));
        }

        return $products;
    }
}
