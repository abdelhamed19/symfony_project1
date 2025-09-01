<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneByEmail($email);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already exists'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $token = $jwtManager->create($user);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['data' => [
            'token' => $token
        ]], 201);
    }
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtManager)
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneByEmail($email);
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['data' => [
            'token' => $token
        ]], 200);
    }
   #[Route('/api/protected', name: 'api_protected', methods: ['GET'])]
    public function protectedRoute(): JsonResponse
    {
        return new JsonResponse(['message' => 'This is a protected route!'], 200);
    }
}
