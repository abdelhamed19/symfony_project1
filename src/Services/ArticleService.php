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
}
