<?php

namespace App\Services;

use App\Entity\Category;
use App\Traits\PaginationTrait;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ArticleService
{
    use PaginationTrait;
    public function __construct(
        private ArticleRepository $articleRepository,
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function listAll($request)
    {
        $data = $this->articleRepository->findAll();
        $data = $this->paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
        return $data;
    }
    public function storeArticle($data, $article)
    {
        $category = $this->entityManager->getRepository(Category::class)->find($data['category'] ?? '');

        if (!$category) {
            return 'Invalid category';
        }
        try {
            $this->entityManager->persist($article);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function showArticle($id)
    {
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return null;
        }
        return $article;
    }
    public function updateArticle()
    {
        try {
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deleteArticle($id)
    {
        try {
            $article = $this->articleRepository->find($id);
            if (!$article) {
                return null;
            }
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
