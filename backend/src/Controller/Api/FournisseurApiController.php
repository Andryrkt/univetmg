<?php

namespace App\Controller\Api;

use App\Entity\Admin\Fournisseur;
use App\Repository\Admin\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/fournisseurs')]
class FournisseurApiController extends AbstractController
{
    #[Route('', name: 'api_fournisseurs_index', methods: ['GET'])]
    public function index(FournisseurRepository $fournisseurRepository): JsonResponse
    {
        $fournisseurs = $fournisseurRepository->findBy([], ['id' => 'DESC']);

        return $this->json(array_map($this->serializeFournisseur(...), $fournisseurs));
    }

    #[Route('/{id}', name: 'api_fournisseurs_show', methods: ['GET'])]
    public function show(Fournisseur $fournisseur): JsonResponse
    {
        return $this->json([
            ...$this->serializeFournisseur($fournisseur),
            'nbProduits' => count($fournisseur->getProduits()),
        ]);
    }

    #[Route('', name: 'api_fournisseurs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $fournisseur = new Fournisseur();
        $this->applyPayload($fournisseur, $data);

        $entityManager->persist($fournisseur);
        $entityManager->flush();

        return $this->json($this->serializeFournisseur($fournisseur), 201);
    }

    #[Route('/{id}', name: 'api_fournisseurs_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Fournisseur $fournisseur, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($fournisseur, $data);
        $entityManager->flush();

        return $this->json($this->serializeFournisseur($fournisseur));
    }

    #[Route('/{id}', name: 'api_fournisseurs_delete', methods: ['DELETE'])]
    public function delete(Fournisseur $fournisseur, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($fournisseur);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data): array
    {
        $errors = [];

        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire.';
        }
        if (empty($data['telephone'])) {
            $errors['telephone'] = 'Le téléphone est obligatoire.';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "L'email n'est pas valide.";
        }

        return $errors;
    }

    private function applyPayload(Fournisseur $fournisseur, array $data): void
    {
        $fournisseur->setNom($data['nom']);
        $fournisseur->setTelephone($data['telephone']);
        $fournisseur->setAdresse($data['adresse'] ?? null);
        $fournisseur->setEmail($data['email'] ?? null);
    }

    private function serializeFournisseur(Fournisseur $fournisseur): array
    {
        return [
            'id' => $fournisseur->getId(),
            'nom' => $fournisseur->getNom(),
            'telephone' => $fournisseur->getTelephone(),
            'adresse' => $fournisseur->getAdresse(),
            'email' => $fournisseur->getEmail(),
        ];
    }
}
