<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentationController extends AbstractController
{
    private const DOCUMENTATIONS = [
        'fonctionnelle' => [
            'label' => 'Documentation Fonctionnelle',
            'icon' => 'fa-users',
            'color' => 'primary',
            'items' => [
                'entites' => [
                    'title' => 'Documentation des Entités',
                    'file' => 'entites.md',
                    'icon' => 'fa-database',
                    'description' => 'Structure et relations des entités de la base de données'
                ],
                'gestion-stock' => [
                    'title' => 'Gestion du Stock',
                    'file' => 'gestion_stock.md',
                    'icon' => 'fa-boxes',
                    'description' => 'Guide complet sur la gestion des stocks et mouvements'
                ],
            ]
        ],
        'technique' => [
            'label' => 'Documentation Technique',
            'icon' => 'fa-code',
            'color' => 'success',
            'items' => [
                'deploiement' => [
                    'title' => 'Code de Déploiement',
                    'file' => 'code_deploiement.md',
                    'icon' => 'fa-rocket',
                    'description' => 'Procédures de déploiement de l\'application'
                ],
                'installation' => [
                    'title' => 'Mémoire d\'Installation',
                    'file' => 'memoire_instalation.md',
                    'icon' => 'fa-download',
                    'description' => 'Guide d\'installation et configuration initiale'
                ],
                'commandes' => [
                    'title' => 'Ligne de Commande',
                    'file' => 'memoire_ligne_de_commande.md',
                    'icon' => 'fa-terminal',
                    'description' => 'Commandes utiles pour le développement'
                ],
                'postgres' => [
                    'title' => 'PostgreSQL',
                    'file' => 'memoire_postgres.md',
                    'icon' => 'fa-server',
                    'description' => 'Configuration et gestion de PostgreSQL'
                ],
            ]
        ]
    ];

    #[Route('/documentation/{slug}', name: 'app_documentation', defaults: ['slug' => 'entites'])]
    public function index(string $slug): Response
    {
        // Rechercher la documentation dans toutes les catégories
        $doc = null;
        $category = null;
        
        foreach (self::DOCUMENTATIONS as $catKey => $catData) {
            if (isset($catData['items'][$slug])) {
                $doc = $catData['items'][$slug];
                $category = $catKey;
                break;
            }
        }
        
        // Vérifier si la documentation existe
        if (!$doc) {
            throw $this->createNotFoundException('Cette documentation n\'existe pas.');
        }

        $docPath = $this->getParameter('kernel.project_dir') . '/documentation/' . $doc['file'];
        
        if (!file_exists($docPath)) {
            throw $this->createNotFoundException('Le fichier de documentation est introuvable.');
        }

        $content = file_get_contents($docPath);

        return $this->render('documentation/index.html.twig', [
            'content' => $content,
            'currentDoc' => $slug,
            'currentCategory' => $category,
            'currentTitle' => $doc['title'],
            'documentations' => self::DOCUMENTATIONS,
        ]);
    }
}

