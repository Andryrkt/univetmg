<?php

namespace App\Controller\Api;

use App\Entity\Produit\Categorie;
use App\Repository\Produit\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories')]
class CategorieApiController extends AbstractController
{
    #[Route('', name: 'api_categories_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): JsonResponse
    {
        $roots = $categorieRepository->findRootCategoriesWithChildren();

        return $this->json(array_map($this->serializeWithChildren(...), $roots));
    }

    #[Route('/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(Categorie $categorie): JsonResponse
    {
        return $this->json([
            ...$this->serializeCategorie($categorie),
            'path' => array_map($this->serializeCategorie(...), $categorie->getPath()),
            'enfants' => array_map($this->serializeCategorie(...), $categorie->getEnfant()->toArray()),
            'nbProduits' => count($categorie->getProduits()),
        ]);
    }

    #[Route('', name: 'api_categories_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, null, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $categorie = new Categorie();
        $this->applyPayload($categorie, $data, $entityManager);

        $entityManager->persist($categorie);
        $entityManager->flush();

        return $this->json($this->serializeCategorie($categorie), 201);
    }

    #[Route('/{id}', name: 'api_categories_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $categorie, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($categorie, $data, $entityManager);
        $entityManager->flush();

        return $this->json($this->serializeCategorie($categorie));
    }

    #[Route('/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
    public function delete(Categorie $categorie, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data, ?Categorie $current, EntityManagerInterface $entityManager): array
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }

        if (!empty($data['parentId'])) {
            $parent = $entityManager->getRepository(Categorie::class)->find($data['parentId']);
            if (!$parent) {
                $errors['parentId'] = 'Catégorie parente introuvable.';
            } elseif ($current && ($parent === $current || in_array($current, $parent->getPath(), true))) {
                $errors['parentId'] = 'Une catégorie ne peut pas être son propre parent ou descendant.';
            }
        }

        return $errors;
    }

    private function applyPayload(Categorie $categorie, array $data, EntityManagerInterface $entityManager): void
    {
        $categorie->setNom($data['nom']);
        $categorie->setAbbreviation($data['abbreviation'] ?? null);
        $categorie->setParent(!empty($data['parentId']) ? $entityManager->getRepository(Categorie::class)->find($data['parentId']) : null);
    }

    private function serializeCategorie(Categorie $categorie): array
    {
        return [
            'id' => $categorie->getId(),
            'nom' => $categorie->getNom(),
            'abbreviation' => $categorie->getAbbreviation(),
            'parent' => $categorie->getParent() ? [
                'id' => $categorie->getParent()->getId(),
                'nom' => $categorie->getParent()->getNom(),
            ] : null,
        ];
    }

    private function serializeWithChildren(Categorie $categorie): array
    {
        return [
            ...$this->serializeCategorie($categorie),
            'enfants' => array_map($this->serializeCategorie(...), $categorie->getEnfant()->toArray()),
        ];
    }
}
