<?php

namespace App\Factory;

use App\Entity\Product;
use Random\RandomException;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Product::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     * @throws RandomException
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(random_int(5, 25)),
            'shortDescription' => self::faker()->text(),
            'longDescription' => self::faker()->text(),
            'price' => self::faker()->randomFloat(2, 1, 1000),
            'picture' => 'https://picsum.photos/seed/' . self::faker()->unique()->numberBetween(1, 1000) . '/400/300',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }
}
