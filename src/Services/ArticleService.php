<?php

namespace App\Services;

use App\Entity\Article;
use App\Kernel;
use App\Entity\Category;
use Symfony\Component\Uid\Uuid;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArticleService
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private EntityManagerInterface $em,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($page)
    {
        $data = $this->em->createQueryBuilder()
            ->select('a AS article')
            ->from(Article::class, 'a')
            ->innerJoin('a.category', 'c')
            ->where('c.deleted = false');

        return $this->paginator->paginate(
            $data,
            $page,
            20,
        );
    }

    private function getUploadPath(): string
    {
        return "{$this->kernel->getProjectDir()}/public/uploads/articles";
    }

    public function uploadImage(Article $article, $uploadedFile): void
    {
        if ($uploadedFile instanceof UploadedFile) {
            $this->removeImage($article);
            $fileName = Uuid::v4();
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if ($extension) {
                $fileName = "{$fileName}.{$extension}";
            }
            $uploadedFile->move($this->getUploadPath(), $fileName);

            $article->setImage($fileName);

            $this->em->flush();
        }
    }

    public function removeImage(Article $article): void
    {
        if ($article->getImage()) {
            $filePath = "{$this->getUploadPath()}/{$article->getImage()}";
            $fs = new Filesystem();
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
            }
        }

        if ($article->getId()) {
            $article->setImage(null);
            $this->em->flush();
        }
    }
}
