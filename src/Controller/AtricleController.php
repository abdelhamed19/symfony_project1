<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Traits\ResponseTrait;
use App\Services\ArticleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/atricles')]
final class AtricleController extends AbstractController
{
    use ResponseTrait;
    public function __construct(private EntityManagerInterface $entityManager, private ArticleService $articleService) {}

    #[Route('/list', name: 'list_atricle',  methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->listAll($request);
        return $this->successData($articles);
    }

    #[Route('/store', name: 'store_atricle',  methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $this->articleService->storeArticle($data, $article);
            if ($article) {
                return $this->successMessage('Article created successfully', 201);
            } else {
                return $this->errorMessage($article, 400);
            }
        }
        return $this->errorMessages(handleValidationError($form), 400);
    }
    #[Route('/show/{id}', name: 'show_atricle',  methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $article = $this->articleService->showArticle($id);
        if (!$article) {
            return $this->errorMessage('Not Found', 404);
        }
        return $this->successData($article);
    }
    #[Route('/update/{id}', name: 'update_atricle',  methods: ['PUT'])]
    public function update(Request $request, $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = $this->entityManager->getRepository(Article::class)->find($id);
        $category = $this->entityManager->getRepository(Category::class)->find((int)$data['category'] ?? '');

        if (!$category || !$article) {
            return $this->errorMessages(['Invalid category or article'], 400);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $this->articleService->updateArticle();
            if ($article) {
                return $this->successData('Article updated successfully');
            }
            return $this->errorMessage('Error updating article', 500);
        }
        return $this->errorMessages(handleValidationError($form), 400);
    }
    #[Route('/delete/{id}', name: 'delete_atricle',  methods: ['DELETE'])]
    public function delete($id)
    {
        $result = $this->articleService->deleteArticle($id);
        if ($result === true) {
            return $this->successMessage('Article deleted successfully');
        }
        return $this->errorMessage($result, 500);
    }
}
