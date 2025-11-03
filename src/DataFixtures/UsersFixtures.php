<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur standard pour tester le login
        $user = new Users();
        $user->setEmail('user@example.com');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);

        $manager->persist($user);

        // Création d'un administrateur
        $admin = new Users();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('System');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        $manager->persist($admin);

        // Création d'un utilisateur non vérifié
        $unverifiedUser = new Users();
        $unverifiedUser->setEmail('unverified@example.com');
        $unverifiedUser->setFirstName('Pierre');
        $unverifiedUser->setLastName('Martin');
        $unverifiedUser->setPassword($this->passwordHasher->hashPassword($unverifiedUser, 'temp123'));
        $unverifiedUser->setRoles(['ROLE_USER']);
        $unverifiedUser->setIsVerified(false);

        $manager->persist($unverifiedUser);

        // Création de plusieurs utilisateurs supplémentaires pour les tests
        for ($i = 1; $i <= 5; $i++) {
            $testUser = new Users();
            $testUser->setEmail("testuser{$i}@example.com");
            $testUser->setFirstName("Prénom{$i}");
            $testUser->setLastName("Nom{$i}");
            $testUser->setPassword($this->passwordHasher->hashPassword($testUser, "test{$i}"));
            $testUser->setRoles(['ROLE_USER']);
            $testUser->setIsVerified($i % 2 === 0); // Alterner entre vérifié et non vérifié

            $manager->persist($testUser);
        }

        $manager->flush();
    }
}
