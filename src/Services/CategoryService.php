<?php

namespace App\Services;

use App\Entity\Article;
use App\Entity\Category;
use App\Traits\FileTrait;
use App\Traits\PaginationTrait;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CategoryService
{
    use PaginationTrait, FileTrait;
    public function __construct(
        private CategoryRepository $categoryRepository,
        private NormalizerInterface $normalizer,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private UrlGeneratorInterface $urlGenerator,
        private ParameterBagInterface $params
    ) {}

    public function listAll($request)
    {
        if ($request->query->get('name')) {
            $data = $this->categoryRepository->findByName($request->query->get('name'));
        } else {
            $data = $this->categoryRepository->findAllOrderedBySortOrder($request->query->get('sort', 'ASC'));
        }
        return $this->paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }
    public function showCategory($id)
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return null;
        }
        return $category;
    }
    public function storeCategory(Category $category)
    {
        try {
            $imageFile = $category->getImageFile();
            if ($imageFile) {
                $category->uploadImage(
                    $imageFile,
                    $this->params->get('app.upload_dir') . Category::IMAGE_DIR,
                    'image',
                    ['image/jpeg', 'image/png'],
                    5 * 1024 * 1024
                );
            }
            $maxSortOrder = $this->categoryRepository->findMaxSortOrder();
            $category->setSortOrder($maxSortOrder + 1);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deleteCategory($id, $dir = null)
    {
        try {
            $category = $this->categoryRepository->find($id);
            if ($category->getImage()) {
                $result = $category->deleteImage($this->params->get('app.upload_dir') . Category::IMAGE_DIR);
            }
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
