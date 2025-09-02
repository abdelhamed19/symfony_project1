<?php

namespace App\Services;

use App\Entity\Article;
use App\Entity\Category;
use App\Traits\PaginationTrait;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryService
{
    use PaginationTrait;
    public function __construct(
        private CategoryRepository $categoryRepository,
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function listAll($request)
    {
        if ($request->query->get('name')) {
            $data = $this->categoryRepository->findByName($request->query->get('name'));
        } else {
            $data = $this->categoryRepository->findAll();
        }
        $data = $this->paginateData($data, $request, ['groups' => ['category:read', 'category:with_articles']], $this->paginator, $this->urlGenerator);
        return $data;
    }
    public function showCategory($id)
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return null;
        }
        return $this->normalizer->normalize($category, null, ['groups' => ['category:read', 'category:with_articles']]);
    }
    public function storeCategory(Category $category)
    {
        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deleteCategory($id)
    {
        try {
            $category = $this->categoryRepository->find($id);
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updateCategory()
    {
        try {
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
