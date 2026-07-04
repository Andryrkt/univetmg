<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserApiController extends AbstractController
{
    private const ALLOWED_ROLES = ['ROLE_USER', 'ROLE_ADMIN'];

    #[Route('', name: 'api_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        return $this->json(array_map($this->serializeUser(...), $userRepository->findAll()));
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($this->serializeUser($user));
    }

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, null, $entityManager, requirePassword: true);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $user = new User();
        $this->applyPayload($user, $data);
        $user->setPassword($passwordHasher->hashPassword($user, $data['plainPassword']));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($this->serializeUser($user), 201);
    }

    #[Route('/{id}', name: 'api_users_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $user, $entityManager, requirePassword: false);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($user, $data);
        if (!empty($data['plainPassword'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['plainPassword']));
        }

        $entityManager->flush();

        return $this->json($this->serializeUser($user));
    }

    #[Route('/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data, ?User $current, EntityManagerInterface $entityManager, bool $requirePassword): array
    {
        $errors = [];

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email est obligatoire et doit être valide.";
        } else {
            $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existing && (!$current || $existing->getId() !== $current->getId())) {
                $errors['email'] = 'Un compte existe déjà avec cet email.';
            }
        }

        $roles = $data['roles'] ?? [];
        if (!is_array($roles) || array_diff($roles, self::ALLOWED_ROLES)) {
            $errors['roles'] = 'Rôles invalides.';
        }

        $password = $data['plainPassword'] ?? null;
        if ($requirePassword && empty($password)) {
            $errors['plainPassword'] = 'Le mot de passe est obligatoire.';
        } elseif (!empty($password) && strlen($password) < 6) {
            $errors['plainPassword'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        return $errors;
    }

    private function applyPayload(User $user, array $data): void
    {
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName'] ?? null);
        $user->setLastName($data['lastName'] ?? null);
        $user->setRoles($data['roles'] ?? []);
        $user->setIsVerified((bool) ($data['isVerified'] ?? false));
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
        ];
    }
}
