<?php

namespace App\Controller;

use App\Entity\Category;
use App\Traits\ResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/categories')]
final class CategoryController extends AbstractController
{
    use ResponseTrait;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/index', name: 'all_categories', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $repository = $this->entityManager->getRepository(Category::class);
        $categories = $repository->findAll();
        foreach ($categories as $key => $category) {
            $categories[$key] = $category->toArray(true);
        }
        return $this->successData($categories);
    }
    #[Route('/create', name: 'create_category', methods: ['POST'])]
    public function store(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $category = new Category();
        $category->setName($data['name'] ?? '');

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = array_map(function ($e) {
                return $e->getMessage();
            }, iterator_to_array($errors));
            return $this->errorMessages($errorMessages, 400);
        }

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $this->successData($category->toArray(), 201);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return $this->errorMessages([$e->getMessage()], 500);
        }
    }
    #[Route('/show/{id}', name: 'show_category', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->errorMessage('Category not found', 404);
        }
        return $this->successData($category->toArray());
    }
    #[Route('/update/{id}', name: 'update_category', methods: ['PATCH'])]
    public function update(Request $request, ValidatorInterface $validator, $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->errorMessage('Category not found', 404);
        }

        $category->setName($data['name'] ?? '');

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = array_map(fn($e) => $e->getMessage(), iterator_to_array($errors));
            return $this->errorMessages($errorMessages, 400);
        }

        try {
            $this->entityManager->flush();
            return $this->successData($category->toArray(), 200);
        } catch (\Exception $e) {
            return $this->errorMessages([$e->getMessage()], 500);
        }
    }

    #[Route('/delete/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->errorMessage('Category not found', 404);
        }
        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $this->successMessage('Category deleted successfully');
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return $this->errorMessage($e->getMessage(), 500);
        }
    }
}
