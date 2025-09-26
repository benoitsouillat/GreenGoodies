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

    private function makeOrder(Order $order): void
    {
        $order->setStatus(OrderStatus::basket)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUser($this->getUser())
                ->setTotalPrice(0)
                ->setOrderNumber(uniqid('ORDER-'));
        $this->manager->persist($order);
        $this->manager->flush();
    }

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

    public function resetOrder(): void
    {
        $currentOrder = $this->getCurrentOrder();
        foreach ($currentOrder->getOrderLines() as $orderLine) {
            $this->manager->remove($orderLine);
        }
        $this->manager->flush();
    }

    private function updateTotalPrice(Order $order): void
    {
        $totalPrice = 0;
        foreach ($order->getOrderLines() as $orderLine) {
            $totalPrice += $orderLine->getProduct()->getUnitPrice() * $orderLine->getQuantity();
        }
        $order->setTotalPrice($totalPrice);
        $this->manager->persist($order);
        $this->manager->flush();
    }

    public function getCurrentOrder(): Order
    {
        $user = $this->getUser();
        $currentOrder = $this->manager->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'status' => OrderStatus::basket,
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

    public function getOrderLine(Product $product): ?OrderLine {
        $line  =$this->getCurrentOrder()->getOrderLines()->filter(
            function (OrderLine $line) use ($product) {
                return $line->getProduct()->getId() === $product->getId();
            })->first();
        return $line === false ? null : $line;
    }

    public function getOrderLineQuantity(Product $product): int {
        $orderLine = $this->getOrderLine($product) ?? null;
        return !($orderLine) ? 0 : $orderLine->getQuantity();
    }

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


}
