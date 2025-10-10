<?php

namespace App\Services;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OrderService
{
    public function __construct(
        public readonly EntityManagerInterface $manager,
        public readonly Security $security,
    ) {}

    private function getUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }

    /**
     * Crée une nouvelle commande pour l'utilisateur connecté avec le statut "cart" et initialise ses propriétés.
     * @param Order $order
     * @return void
     */
    private function makeOrder(Order $order): void
    {
        /** Génère un nombre aléatoire à partir de la date et de mt_rand | résultat plus propre que uniqid() **/
        $datePart = (new \DateTime())->format('Ymd-His');
        $randomPart = mt_rand(10, 99);

        $order->setStatus(OrderStatus::cart)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUser($this->getUser())
                ->setTotalPrice(0)
                ->setOrderNumber($datePart . '-' . $randomPart);
        $this->manager->persist($order);
        $this->manager->flush();
    }

    /**
     * Met à jour le prix total de la commande en fonction des lignes de commande associées.
     * @param Order $order
     * @return void
     */
    private function updateTotalPrice(Order $order): void
    {
        $totalPrice = 0;
        foreach ($order->getOrderLines() as $orderLine) {
            $totalPrice += $orderLine->getProduct()->getPrice() * $orderLine->getQuantity();
        }
        $order->setTotalPrice($totalPrice);
        $this->manager->persist($order);
        $this->manager->flush();
    }

    /**
     * Valide la commande en cours de l'utilisateur connecté en changeant son statut et en définissant la date de validation.
     * @return bool Retourne true si la commande a été validée avec succès, false si aucune ligne de la commande n'existe.
     */
    public function validateOrder(): bool
    {
        $order = $this->getCurrentOrder();
        if ($order->getOrderLines()->isEmpty()) {
            return false;
        }
        $order->setStatus(OrderStatus::validated)
            ->setValidatedAt(new \DateTimeImmutable());
        $this->manager->persist($order);
        $this->manager->flush();
        return true;
    }

    /**
     * Réinitialise la commande en cours en supprimant toutes ses lignes de la commande.
     * @return void
     */
    public function resetOrder(): void
    {
        $currentOrder = $this->getCurrentOrder();
        foreach ($currentOrder->getOrderLines() as $orderLine) {
            $this->manager->remove($orderLine);
        }
        $this->manager->flush();
    }

    /**
     * Récupère la commande en cours pour l'utilisateur connecté ou en crée une nouvelle si aucune n'existe.
     * @return Order|null
     */
    public function getCurrentOrder(): ?Order
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }
        $currentOrder = $this->manager->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'status' => OrderStatus::cart,
        ]);

        if (!$currentOrder) {
            $currentOrder = new Order();
            $this->makeOrder($currentOrder);
        }
        else {
            $this->updateTotalPrice($currentOrder);
            $this->manager->persist($currentOrder);
            $this->manager->flush();
        }
        return $currentOrder;
    }

    /**
     * Récupère la ligne de la commande pour un produit donné dans la commande en cours pour l'utilisateur connecté.
     * @param Product $product
     * @return OrderLine|null
     */
    public function getOrderLine(Product $product): ?OrderLine {
        $order = $this->getCurrentOrder();
        if (!$order) {
            return null;
        }
        $line = $order->getOrderLines()->filter(
            function (OrderLine $line) use ($product) {
                return $line->getProduct()->getId() === $product->getId();
            })->first();
        return $line === false ? null : $line;
    }

    /**
     * Récupère la quantité d'un produit donné dans la commande en cours de l'utilisateur connecté.
     * @param Product $product
     * @return int
     */
    public function getOrderLineQuantity(Product $product): int {
        $orderLine = $this->getOrderLine($product) ?? null;
        return !($orderLine) ? 0 : $orderLine->getQuantity();
    }

    /**
     * Met à jour la quantité d'un produit dans la commande en cours de l'utilisateur connecté.
     * @param Product $product
     * @param int $quantity
     * @return OrderLine
     */
    public function setOrderLineQuantity(Product $product, int $quantity): OrderLine
    {
        $orderLine = $this->getOrderLine($product) ?? new OrderLine();
        $orderLine->setProduct($product)
            ->setOrderRel($this->getCurrentOrder())
            ->setQuantity($quantity);
        $this->manager->persist($orderLine);
        $this->manager->flush();
        $this->updateTotalPrice($this->getCurrentOrder());
        return $orderLine;
    }

    /**
     * Supprime la ligne de commande pour un produit donné dans la commande en cours de l'utilisateur connecté.
     * @param Product $product
     * @return void
     */
    public function removeOrderLine(Product $product): void
    {
        $orderLine = $this->getOrderLine($product);
        if ($orderLine) {
            $this->manager->remove($orderLine);
            $this->manager->flush();
            $this->updateTotalPrice($this->getCurrentOrder());
        }
    }

}
