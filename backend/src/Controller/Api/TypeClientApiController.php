<?php

namespace App\Controller\Api;

use App\Entity\Vente\TypeClient;
use App\Repository\Vente\TypeClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/type-clients')]
class TypeClientApiController extends AbstractController
{
    #[Route('', name: 'api_type_clients_index', methods: ['GET'])]
    public function index(TypeClientRepository $typeClientRepository): JsonResponse
    {
        $typeClients = $typeClientRepository->findBy([], ['nom' => 'ASC']);

        return $this->json(array_map($this->serializeTypeClient(...), $typeClients));
    }

    #[Route('/{id}', name: 'api_type_clients_show', methods: ['GET'])]
    public function show(TypeClient $typeClient): JsonResponse
    {
        return $this->json([
            ...$this->serializeTypeClient($typeClient),
            'nbClients' => count($typeClient->getClients()),
        ]);
    }

    #[Route('', name: 'api_type_clients_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $typeClient = new TypeClient();
        $this->applyPayload($typeClient, $data);

        $entityManager->persist($typeClient);
        $entityManager->flush();

        return $this->json($this->serializeTypeClient($typeClient), 201);
    }

    #[Route('/{id}', name: 'api_type_clients_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, TypeClient $typeClient, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($typeClient, $data);
        $entityManager->flush();

        return $this->json($this->serializeTypeClient($typeClient));
    }

    #[Route('/{id}', name: 'api_type_clients_delete', methods: ['DELETE'])]
    public function delete(TypeClient $typeClient, EntityManagerInterface $entityManager): JsonResponse
    {
        if (count($typeClient->getClients()) > 0) {
            return $this->json([
                'message' => sprintf('Impossible de supprimer ce type de client car il est utilisé par %d client(s).', count($typeClient->getClients())),
            ], 409);
        }

        $entityManager->remove($typeClient);
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
        if (!isset($data['tauxRemise']) || !is_numeric($data['tauxRemise'])) {
            $errors['tauxRemise'] = 'Le taux de remise est obligatoire.';
        } elseif ($data['tauxRemise'] < 0 || $data['tauxRemise'] > 100) {
            $errors['tauxRemise'] = 'Le taux de remise doit être compris entre 0 et 100.';
        }

        return $errors;
    }

    private function applyPayload(TypeClient $typeClient, array $data): void
    {
        $typeClient->setNom($data['nom']);
        $typeClient->setTauxRemise((string) $data['tauxRemise']);
        $typeClient->setDescription($data['description'] ?? null);
        $typeClient->setActif((bool) ($data['actif'] ?? true));
    }

    private function serializeTypeClient(TypeClient $typeClient): array
    {
        return [
            'id' => $typeClient->getId(),
            'nom' => $typeClient->getNom(),
            'tauxRemise' => $typeClient->getTauxRemise(),
            'description' => $typeClient->getDescription(),
            'actif' => $typeClient->isActif(),
        ];
    }
}
