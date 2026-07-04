<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin_user';
    public const USER_USER_REFERENCE = 'user_user';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Création d'un administrateur
        $admin = new User();
        $admin->setEmail('admin@univet.com'); // Email unique pour l'admin
        $admin->setFirstName('Admin');
        $admin->setLastName('Univet');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $manager->persist($admin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Création d'un utilisateur standard
        $user = new User();
        $user->setEmail('user@univet.com'); // Email unique pour l'utilisateur
        $user->setFirstName('Utilisateur');
        $user->setLastName('Standard');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        $manager->persist($user);
        $this->addReference(self::USER_USER_REFERENCE, $user);

        // Création de plusieurs utilisateurs supplémentaires pour les tests
        for ($i = 0; $i < 10; $i++) {
            $testUser = new User();
            $testUser->setEmail($faker->unique()->safeEmail());
            $testUser->setFirstName($faker->firstName());
            $testUser->setLastName($faker->lastName());
            $testUser->setPassword($this->passwordHasher->hashPassword($testUser, 'password'));
            $testUser->setRoles($faker->boolean(20) ? ['ROLE_ADMIN'] : ['ROLE_USER']); // 20% d'admins
            $testUser->setIsVerified($faker->boolean(80)); // 80% vérifiés

            $manager->persist($testUser);
        }

        $manager->flush();
    }
}
