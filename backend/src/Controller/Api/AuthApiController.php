<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/api')]
class AuthApiController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        #[Autowire(env: 'FRONTEND_URL')] private readonly string $frontendUrl,
    ) {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = [];
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email est obligatoire et doit être valide.";
        } elseif ($entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
            $errors['email'] = 'Un compte existe déjà avec cet email.';
        }
        $password = $data['password'] ?? '';
        if (strlen($password) < 6) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName'] ?? null);
        $user->setLastName($data['lastName'] ?? null);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $entityManager->persist($user);
        $entityManager->flush();

        $this->emailVerifier->sendEmailConfirmation('api_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('no-reply@univet.mg', 'univet.registration'))
                ->to((string) $user->getEmail())
                ->subject('Confirmez votre adresse email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        return $this->json([
            'message' => 'Inscription réussie. Vérifiez votre boîte mail pour confirmer votre adresse.',
        ], 201);
    }

    #[Route('/verify-email', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request, UserRepository $userRepository): RedirectResponse
    {
        $userId = $request->query->get('id');
        $user = $userId ? $userRepository->find($userId) : null;

        if (!$user) {
            return new RedirectResponse($this->frontendUrl.'/verify-email?status=error&message=Utilisateur+introuvable');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            return new RedirectResponse($this->frontendUrl.'/verify-email?status=error&message='.urlencode($exception->getReason()));
        }

        return new RedirectResponse($this->frontendUrl.'/verify-email?status=success');
    }
}
