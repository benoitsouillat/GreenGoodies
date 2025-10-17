<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'apiAccess' => self::faker()->boolean(),
            'email' => self::faker()->unique()->safeEmail(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'password' => 'password',
            'roles' => ['ROLE_USER'],
        ];
    }

    /**
     * Crée un utilisateur administrateur par défaut
     */
    public static function createAdmin(): User
    {
        return self::new()
            ->create([
                'email' => 'admin@johndoe.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'roles' => ['ROLE_ADMIN'],
                'password' => 'admin',
                'apiAccess' => true,
            ])
            ->_real();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        // Mise à jour de l'utilisateur avant sa sauvegarde
        return $this->afterInstantiate(function(User $user): void {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
        });
    }
}
