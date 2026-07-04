<?php

namespace App\Controller\Api;

use App\Entity\Vente\Client;
use App\Entity\Vente\TypeClient;
use App\Repository\Vente\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/clients')]
class ClientApiController extends AbstractController
{
    #[Route('', name: 'api_clients_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): JsonResponse
    {
        $clients = $clientRepository->findBy([], ['id' => 'DESC']);

        return $this->json(array_map($this->serializeClient(...), $clients));
    }

    #[Route('/{id}', name: 'api_clients_show', methods: ['GET'])]
    public function show(Client $client): JsonResponse
    {
        return $this->json([
            ...$this->serializeClient($client),
            'nbVentes' => count($client->getVentes()),
        ]);
    }

    #[Route('', name: 'api_clients_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $client = new Client();
        $this->applyPayload($client, $data, $entityManager);

        $entityManager->persist($client);
        $entityManager->flush();

        return $this->json($this->serializeClient($client), 201);
    }

    #[Route('/{id}', name: 'api_clients_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Client $client, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = $this->validatePayload($data, $entityManager);
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        $this->applyPayload($client, $data, $entityManager);
        $entityManager->flush();

        return $this->json($this->serializeClient($client));
    }

    #[Route('/{id}', name: 'api_clients_delete', methods: ['DELETE'])]
    public function delete(Client $client, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($client);
        $entityManager->flush();

        return $this->json(null, 204);
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
        if (!empty($data['typeClientId']) && !$entityManager->getRepository(TypeClient::class)->find($data['typeClientId'])) {
            $errors['typeClientId'] = 'Type de client introuvable.';
        }

        return $errors;
    }

    private function applyPayload(Client $client, array $data, EntityManagerInterface $entityManager): void
    {
        $client->setNom($data['nom']);
        $client->setTelephone($data['telephone'] ?? null);
        $client->setAdresse($data['adresse'] ?? null);
        $client->setTypeClient(!empty($data['typeClientId']) ? $entityManager->getRepository(TypeClient::class)->find($data['typeClientId']) : null);
    }

    private function serializeClient(Client $client): array
    {
        return [
            'id' => $client->getId(),
            'nom' => $client->getNom(),
            'telephone' => $client->getTelephone(),
            'adresse' => $client->getAdresse(),
            'typeClient' => $client->getTypeClient() ? [
                'id' => $client->getTypeClient()->getId(),
                'nom' => $client->getTypeClient()->getNom(),
            ] : null,
        ];
    }
}
