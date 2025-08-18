<?php

namespace App\Controller;

use App\Entity\Key;
use App\Repository\KeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
#[OA\Tag(name: 'Keys')] // This groups our endpoints in Swagger
class KeyController extends AbstractController
{
    // --- 1. The "List All Keys" Endpoint (GET) ---
    #[Route('/keys', name: 'api_keys_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of all keys.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'keyIdentifier', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                ],
                type: 'object'
            )
        )
    )]
    public function list(KeyRepository $keyRepository): JsonResponse
    {
        $keys = $keyRepository->findAll();
        $data = [];

        foreach ($keys as $key) {
            $data[] = [
                'id' => $key->getId(),
                'keyIdentifier' => $key->getKeyIdentifier(),
                'status' => $key->getStatus(),
                'createdAt' => $key->getCreatedAt()->format('Y-m-d H:i:s'), // Format the datetime
            ];
        }

        return $this->json($data);
    }

    #[OA\RequestBody(
        description: 'The data needed to create a new Key',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'keyIdentifier', type: 'string', example: 'A1-B2-C3-D4'),
            ]
        )
    )]
    #[Route('/keys', name: 'api_keys_create', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Key created successfully.'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request, invalid input.'
    )]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['keyIdentifier'])) {
            return $this->json(['error' => 'keyIdentifier is required'], Response::HTTP_BAD_REQUEST);
        }

        $key = new Key();
        $key->setKeyIdentifier($data['keyIdentifier']);
        // The Key entity's constructor already sets the status to 'active' by default.

        $em->persist($key);
        $em->flush();

        $responseData = [
            'id' => $key->getId(),
            'keyIdentifier' => $key->getKeyIdentifier(),
            'status' => $key->getStatus(),
            'createdAt' => $key->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return $this->json($responseData, Response::HTTP_CREATED);
    }

    #[Route('/keys/{id}', name: 'api_keys_update', methods: ['PATCH'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the key to update.',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'The new status for the Key.',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Key updated successfully.'
    )]
    #[OA\Response(
        response: 404,
        description: 'Key not found.'
    )]
    #[OA\Response(
        response: 400,
        description: 'Bad request, invalid status provided.'
    )]
    public function update(Request $request, Key $key, EntityManagerInterface $em): JsonResponse
    {
        // The "Key $key" argument automatically fetches the Key from the DB by its ID.
        // If not found, Symfony automatically sends a 404 error. This is ParamConverter.
        $data = json_decode($request->getContent(), true);

        if (empty($data['status']) || !in_array($data['status'], ['active', 'inactive'])) {
            return $this->json(['error' => 'A valid status (active/inactive) is required'], Response::HTTP_BAD_REQUEST);
        }

        $key->setStatus($data['status']);
        $em->flush(); // Save the changes to the database

        return $this->json([
            'id' => $key->getId(),
            'keyIdentifier' => $key->getKeyIdentifier(),
            'status' => $key->getStatus(),
        ]);
    }

    // --- 4. The "Delete a Key" Endpoint (DELETE) ---
    #[Route('/keys/{id}', name: 'api_keys_delete', methods: ['DELETE'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the key to delete.',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Key deleted successfully.'
    )]
    #[OA\Response(
        response: 404,
        description: 'Key not found.'
    )]
    public function delete(Key $key, EntityManagerInterface $em): JsonResponse
    {
        // ParamConverter finds the Key for us.
        $em->remove($key);
        $em->flush();

        // A 204 response should have no content, so we return an empty JsonResponse.
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/keys/{id}', name: 'api_keys_get', methods: ['GET'])]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the key to retrieve.',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the requested key.'
    )]
    #[OA\Response(
        response: 404,
        description: 'Key not found.'
    )]
    public function getOne(Key $key): JsonResponse
    {
        // The ParamConverter automatically finds the key or returns a 404.
        $data = [
            'id' => $key->getId(),
            'keyIdentifier' => $key->getKeyIdentifier(),
            'status' => $key->getStatus(),
            'createdAt' => $key->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $key->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];

        return $this->json($data);
    }
}
