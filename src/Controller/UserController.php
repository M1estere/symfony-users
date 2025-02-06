<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Annotations as OA;

final class UserController extends AbstractController
{
    #[Route('/api/users/register', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder): Response
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
        $user->setPassword($passwordEncoder->encodePassword($user, $data['password']));

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['id' => $user->getId(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
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
    public function loginUser(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
        }

        // Логика авторизации

        return new JsonResponse(['message' => 'User logged in successfully']);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
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
