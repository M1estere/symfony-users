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

final class UserController extends AbstractController
{
    #[Route('/api/users/register', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordEncoder->encodePassword($user, $data['password']));

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['id' => $user->getId(), 'email' => $user->getEmail()], Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    public function updateUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $user->setPassword($data['password']); // Не забудьте закодировать пароль
        }

        $em->flush();

        return new JsonResponse(['message' => 'User updated successfully']);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/login', methods: ['POST'])]
    public function loginUser(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Валидация данных
        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
        }

        // Логика авторизации (например, проверка пользователя и генерация токена)
        // Здесь вы можете использовать JWT или другой метод авторизации

        return new JsonResponse(['message' => 'User logged in successfully']);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    public function getUserById(User $user): Response // Переименованный метод
    {
        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ]);
    }

    #[Route('/test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse(['message' => 'Test route works!']);
    }
}
