<?php

namespace App\Controller\Api;

use League\CommonMark\CommonMarkConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/documentation')]
class DocumentationApiController extends AbstractController
{
    private const DOCUMENTATIONS = [
        'fonctionnelle' => [
            'label' => 'Documentation Fonctionnelle',
            'items' => [
                'entites' => [
                    'title' => 'Documentation des Entités',
                    'file' => 'entites.md',
                    'description' => 'Structure et relations des entités de la base de données',
                ],
                'gestion-stock' => [
                    'title' => 'Gestion du Stock',
                    'file' => 'gestion_stock.md',
                    'description' => 'Guide complet sur la gestion des stocks et mouvements',
                ],
            ],
        ],
        'technique' => [
            'label' => 'Documentation Technique',
            'items' => [
                'deploiement' => [
                    'title' => 'Code de Déploiement',
                    'file' => 'code_deploiement.md',
                    'description' => 'Procédures de déploiement de l\'application',
                ],
                'installation' => [
                    'title' => 'Mémoire d\'Installation',
                    'file' => 'memoire_instalation.md',
                    'description' => 'Guide d\'installation et configuration initiale',
                ],
                'commandes' => [
                    'title' => 'Ligne de Commande',
                    'file' => 'memoire_ligne_de_commande.md',
                    'description' => 'Commandes utiles pour le développement',
                ],
                'postgres' => [
                    'title' => 'PostgreSQL',
                    'file' => 'memoire_postgres.md',
                    'description' => 'Configuration et gestion de PostgreSQL',
                ],
            ],
        ],
    ];

    #[Route('', name: 'api_documentation_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $result = [];
        foreach (self::DOCUMENTATIONS as $catKey => $catData) {
            $result[$catKey] = [
                'label' => $catData['label'],
                'items' => array_map(
                    fn (string $slug, array $item) => ['slug' => $slug, 'title' => $item['title'], 'description' => $item['description']],
                    array_keys($catData['items']),
                    $catData['items']
                ),
            ];
        }

        return $this->json($result);
    }

    #[Route('/{slug}', name: 'api_documentation_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $doc = null;
        $category = null;

        foreach (self::DOCUMENTATIONS as $catKey => $catData) {
            if (isset($catData['items'][$slug])) {
                $doc = $catData['items'][$slug];
                $category = $catKey;
                break;
            }
        }

        if (!$doc) {
            throw $this->createNotFoundException('Cette documentation n\'existe pas.');
        }

        $docPath = $this->getParameter('kernel.project_dir').'/documentation/'.$doc['file'];

        if (!file_exists($docPath)) {
            throw $this->createNotFoundException('Le fichier de documentation est introuvable.');
        }

        $converter = new CommonMarkConverter();

        return $this->json([
            'slug' => $slug,
            'category' => $category,
            'title' => $doc['title'],
            'contentHtml' => (string) $converter->convert(file_get_contents($docPath)),
        ]);
    }
}
