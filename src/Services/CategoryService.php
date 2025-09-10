<?php

namespace App\Services;

use App\Kernel;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($page)
    {
        $qb = $this
            ->em
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.deleted = false');

        $query = $qb->getQuery();
        return $this->paginator->paginate($query, $page, 20);
    }

    public function findMaxSortOrder()
    {
        return $this->categoryRepository->findMaxSortOrder();
    }

    private function getUploadPath(): string
    {
        return "{$this->kernel->getProjectDir()}/public/uploads/category";
    }

    public function uploadImage(Category $category, $uploadedFile): void
    {
        if ($uploadedFile instanceof UploadedFile) {
            $this->removeImage($category);
            $fileName = Uuid::v4();
            $size = $uploadedFile->getSize();
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if ($extension) {
                $fileName = "{$fileName}.{$extension}";
            }
            $uploadedFile->move($this->getUploadPath(), $fileName);

            $category
                ->setImage($fileName)
                ->setImageType($extension)
                ->setImageSize($size);

            $this->em->flush();
        }
    }

    public function removeImage(Category $category): void
    {
        if ($category->getImage()) {
            $filePath = "{$this->getUploadPath()}/{$category->getImage()}";
            $fs = new Filesystem();
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
            }
        }

        if ($category->getId()) {
            $category->setImage(null)
                ->setImageType(null)
                ->setImageSize(null);
            $this->em->flush();
        }
    }

}
