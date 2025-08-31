<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ResponseTrait
{
    public function successData($data = [], int $status = 200): JsonResponse
    {
        return new JsonResponse(['data' => $data], $status);
    }
    public function successMessage(string $message, int $status = 200): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
    public function errorMessages(array $messages, int $status = 400): JsonResponse
    {
        return new JsonResponse(['messages' => $messages], $status);
    }
    public function errorMessage(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
}
