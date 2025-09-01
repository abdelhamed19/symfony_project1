<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Traits\ResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/atricles')]
final class AtricleController extends AbstractController
{
    use ResponseTrait;
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/list', name: 'list_atricle',  methods: ['GET'])]
    public function index(): JsonResponse
    {
        $repository = $this->entityManager->getRepository(Article::class);
        $articles = $repository->findAll();
        foreach ($articles as $key => $value) {
            $articles[$key] = $value->toArray(true);
        }
        return $this->successData($articles);
    }
    #[Route('/store', name: 'store_atricle',  methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $category = $this->entityManager->getRepository(Category::class)->find($data['category'] ?? '');

        if (!$category) {
            return $this->errorMessages(['Invalid category'], 400);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->beginTransaction();

            try {
                $this->entityManager->persist($article);
                $this->entityManager->flush();

                $this->entityManager->commit();

                return $this->successData($article->toArray(true), 201);
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                return $this->errorMessages([$e->getMessage()], 500);
            }
        }
        return $this->errorMessages(handleValidationError($form), 400);
    }
    #[Route('/show/{id}', name: 'show_atricle',  methods: ['GET'])]
    public function show($id): JsonResponse
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->errorMessage('Not Found', 404);
        }
        return $this->successData($article->toArray(true));
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
            $this->entityManager->beginTransaction();
            try {
                $this->entityManager->flush();
                $this->entityManager->commit();
                return $this->successData($article->toArray(true));
            } catch (\Exception $e) {
                $this->entityManager->rollback();
                return $this->errorMessage($e->getMessage(), 500);
            }
        }
        return $this->errorMessages(handleValidationError($form), 400);
    }
    #[Route('/delete/{id}', name: 'delete_atricle',  methods: ['DELETE'])]
    public function delete($id)
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            return $this->errorMessage('Article Not Found', 404);
        }
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return $this->successMessage('Article deleted successfully');
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return $this->errorMessage($e->getMessage(), 500);
        }
    }
}
