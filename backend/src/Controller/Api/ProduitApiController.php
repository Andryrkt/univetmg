<?php

namespace App\Controller\Api;

use App\Entity\Admin\Fournisseur;
use App\Entity\Produit\Categorie;
use App\Entity\Produit\Produit;
use App\Entity\Unite\Unite;
use App\Repository\Produit\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/produits')]
class ProduitApiController extends AbstractController
{
    #[Route('', name: 'api_produits_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $query = $request->query->get('q');

        $produits = $query
            ? $produitRepository->searchByNameOrCode($query)
            : $produitRepository->findBy([], ['id' => 'DESC']);

        return $this->json(array_map($this->serializeProduit(...), $produits));
    }

    #[Route('/{id}', name: 'api_produits_show', methods: ['GET'])]
    public function show(Produit $produit): JsonResponse
    {
        return $this->json($this->serializeProduit($produit));
    }

    #[Route('', name: 'api_produits_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $produit = new Produit();
        $this->applyPayload($produit, $data, $entityManager);

        $entityManager->persist($produit);
        $entityManager->flush();

        return $this->json($this->serializeProduit($produit), 201);
    }

    #[Route('/{id}', name: 'api_produits_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Produit $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($produit, $data, $entityManager);
        $entityManager->flush();

        return $this->json($this->serializeProduit($produit));
    }

    #[Route('/{id}', name: 'api_produits_delete', methods: ['DELETE'])]
    public function delete(Produit $produit, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    #[Route('/{id}/unites', name: 'api_produit_unites', methods: ['GET'])]
    public function getUnites(Produit $produit): JsonResponse
    {
        $unites = [];

        if ($produit->getUniteDeBase()) {
            $unites[] = [
                'id' => $produit->getUniteDeBase()->getId(),
                'nom' => $produit->getUniteDeBase()->getNom(),
                'symbole' => $produit->getUniteDeBase()->getSymbole(),
                'facteur' => 1.0,
                'isBase' => true,
            ];
        }

        foreach ($produit->getConditionnements() as $conditionnement) {
            if ($conditionnement->getUnite()) {
                $unites[] = [
                    'id' => $conditionnement->getUnite()->getId(),
                    'nom' => $conditionnement->getUnite()->getNom(),
                    'symbole' => $conditionnement->getUnite()->getSymbole(),
                    'facteur' => $conditionnement->getQuantite(),
                    'prix' => $conditionnement->getPrixVente(),
                    'isBase' => false,
                ];
            }
        }

        return $this->json($unites);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data, EntityManagerInterface $entityManager): array
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (!isset($data['stockMinimum']) || !is_numeric($data['stockMinimum'])) {
            $errors['stockMinimum'] = 'Le stock minimum est obligatoire.';
        }
        if (empty($data['uniteDeBaseId'])) {
            $errors['uniteDeBaseId'] = "L'unité de base est obligatoire.";
        } elseif (!$entityManager->getRepository(Unite::class)->find($data['uniteDeBaseId'])) {
            $errors['uniteDeBaseId'] = 'Unité introuvable.';
        }
        if (!empty($data['categorieId']) && !$entityManager->getRepository(Categorie::class)->find($data['categorieId'])) {
            $errors['categorieId'] = 'Catégorie introuvable.';
        }
        if (!empty($data['fournisseurId']) && !$entityManager->getRepository(Fournisseur::class)->find($data['fournisseurId'])) {
            $errors['fournisseurId'] = 'Fournisseur introuvable.';
        }

        return $errors;
    }

    private function applyPayload(Produit $produit, array $data, EntityManagerInterface $entityManager): void
    {
        $produit->setNom($data['nom']);
        $produit->setDescription($data['description'] ?? null);
        $produit->setStockMinimum((float) $data['stockMinimum']);
        $produit->setPrixVente(isset($data['prixVente']) ? (float) $data['prixVente'] : null);
        $produit->setUniteDeBase($entityManager->getRepository(Unite::class)->find($data['uniteDeBaseId']));
        $produit->setCategorie(!empty($data['categorieId']) ? $entityManager->getRepository(Categorie::class)->find($data['categorieId']) : null);
        $produit->setFournisseur(!empty($data['fournisseurId']) ? $entityManager->getRepository(Fournisseur::class)->find($data['fournisseurId']) : null);
    }

    private function serializeProduit(Produit $produit): array
    {
        return [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'description' => $produit->getDescription(),
            'code' => $produit->getCode(),
            'stockMinimum' => $produit->getStockMinimum(),
            'prixVente' => $produit->getPrixVente(),
            'quantiteEnStock' => $produit->getQuantiteEnStock(),
            'uniteDeBase' => $produit->getUniteDeBase() ? [
                'id' => $produit->getUniteDeBase()->getId(),
                'nom' => $produit->getUniteDeBase()->getNom(),
                'symbole' => $produit->getUniteDeBase()->getSymbole(),
            ] : null,
            'categorie' => $produit->getCategorie() ? [
                'id' => $produit->getCategorie()->getId(),
                'nom' => $produit->getCategorie()->getNom(),
            ] : null,
            'fournisseur' => $produit->getFournisseur() ? [
                'id' => $produit->getFournisseur()->getId(),
                'nom' => $produit->getFournisseur()->getNom(),
            ] : null,
        ];
    }
}
