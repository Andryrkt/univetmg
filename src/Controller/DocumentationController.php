<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    #[Route('/documentation', name: 'app_documentation')]
    public function index(): Response
    {
        $docPath = $this->getParameter('kernel.project_dir') . '/documentation/entites.md';
        
        if (!file_exists($docPath)) {
            throw $this->createNotFoundException('La documentation est introuvable.');
        }

        $content = file_get_contents($docPath);

        return $this->render('documentation/index.html.twig', [
            'content' => $content,
        ]);
    }
}
