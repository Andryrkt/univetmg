<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // if ($error) {
        //     $user = $userRepository->findBy([]);
        //     dump($user);
        //     dump($lastUsername);
        //     $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        //     dump($user);
        //     if (!$user) {
        //         dd("DEBUG: L'utilisateur avec l'email $lastUsername n'a pas été trouvé dans la base de données.");
        //     } else {
        //         dd("DEBUG: L'utilisateur $lastUsername a été trouvé, donc le mot de passe est incorrect.");
        //     }
        // }

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
