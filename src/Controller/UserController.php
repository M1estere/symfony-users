<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/api/users/register", methods: ["POST"])]
    #[OA\Post(
        path: "/api/users/register",
        summary: "Register a new user",
        description: "This endpoint allows a new user to register by providing an email and password.",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", minLength: 8, example: "strongpassword"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid data"),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "User already exists",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "User already exists."),
                    ]
                )
            )
        ]
    )]
    public function createUser(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['id' => $user->getId(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update an existing user',
        description: 'This endpoint allows updating user details by providing user ID, email, and/or password.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the user to update'
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'newstrongpassword'),
                    ]
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'User updated successfully'),
                        ]
                    )
                ]
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: 'Invalid email format.'),
                        ]
                    )
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                        ]
                    )
                ]
            )
        ]
    )]
    public function updateUser(Request $request, ?User $user, EntityManagerInterface $em): Response
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'Invalid email format.'], Response::HTTP_BAD_REQUEST);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'User updated successfully']);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Delete an existing user',
        description: 'This endpoint allows deleting a user by providing user ID.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the user to delete'
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'User deleted successfully',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'User deleted successfully'),
                        ]
                    )
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                        ]
                    )
                ]
            )
        ]
    )]
    public function deleteUser(?User $user, EntityManagerInterface $em): Response
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users/login',
        summary: 'User login',
        description: 'This endpoint allows a user to log in by providing email and password.',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'application/json' => new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', example: 'strongpassword'),
                    ]
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User logged in successfully',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'User logged in successfully'),
                        ]
                    )
                ]
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid credentials',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: 'Invalid credentials'),
                        ]
                    )
                ]
            )
        ]
    )]
    public function loginUser(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user || $data['password'] !== $user->getPassword()) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['message' => 'User logged in successfully']);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Get user by ID',
        description: 'This endpoint retrieves user details by providing the user ID.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID of the user to retrieve'
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User found',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        ]
                    )
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: [
                    'application/json' => new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'error', type: 'string', example: 'User not found'),
                        ]
                    )
                ]
            )
        ]
    )]
    public function getUserById(?User $user): Response
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ]);
    }
}
