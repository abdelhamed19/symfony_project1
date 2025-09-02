<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Traits\ResponseTrait;
use App\Services\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/categories')]
final class CategoryController extends AbstractController
{
    use ResponseTrait;

    public function __construct(private CategoryService $categoryService, private EntityManagerInterface $entityManager) {}
    #[Route('/index', name: 'all_categories', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $data = $this->categoryService->listAll($request);
        return $this->successData($data);
    }
    #[Route('/create', name: 'create_category', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        // form-data -> $request->request->all()
        // raw/json -> json_decode($request->getContent(), true)
        $data = $request->request->all();

        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $this->categoryService->storeCategory($category);
            if ($category) {
                return $this->successData('Category created successfully', 201);
            }
            return $this->errorMessage('Error creating category', 500);
        }

        return $this->errorMessages(handleValidationError($form), 400);
    }

    #[Route('/show/{id}', name: 'show_category', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $data = $this->categoryService->showCategory($id);
        if (!$data) {
            return $this->errorMessage('Category not found', 404);
        }
        return $this->successData($data);
    }
    #[Route('/update/{id}', name: 'update_category', methods: ['PUT'])]
    public function update(Request $request, $id): JsonResponse
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->errorMessage('Category not found', 404);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(CategoryType::class, $category);
        $form->submit($data);

        if ($form->isValid() && $form->isSubmitted()) {
            $category = $this->categoryService->updateCategory($category);
            if ($category) {
                return $this->successData('Category updated successfully', 200);
            }
            return $this->errorMessage('Error updating category', 500);
        }
        return $this->errorMessages(handleValidationError($form), 400);
    }

    #[Route('/delete/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function destroy(int $id): JsonResponse
    {
        $result = $this->categoryService->deleteCategory($id);
        if ($result === null) {
            return $this->errorMessage('Error Deleting Category', 404);
        } else {
            return $this->successMessage('Category deleted successfully', 200);
        }
    }
}
