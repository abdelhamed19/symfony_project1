<?php

namespace App\Job\MessageHandler;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use App\Job\Message\CategoryDeleteMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CategoryDeleteMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}
    
    public function __invoke(CategoryDeleteMessage $message)
    {
        $categoryId = $message->getCategoryId();
        $category = $this->em->getRepository(Category::class)->find($categoryId);
        if ($category) {
            $articles = $this->em->getRepository(Article::class)->findBy([
                'category' => $category
            ]);
            foreach ($articles as $article) {
                $this->em->remove($article);
                $this->em->flush();
            }

            $this->em->remove($category);
            $this->em->flush();
        }
    }
}
