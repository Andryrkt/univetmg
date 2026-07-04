<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_public')]
    public function publicHome(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home_private');
        }
        return $this->render('home/public.html.twig');
    }

    #[Route('/home', name: 'app_home_private')]
    public function privateHome(): Response
    {
        return $this->render('home/private.html.twig');
    }
}
