<?php

namespace App\EventListener;

use App\Services\ArticleService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ArticleListener
{
    public function __construct(private ArticleService $articleService) {}
    public function postRemove(LifecycleEventArgs $arg)
    {
        $entity = $arg->getObject();
        if ($entity instanceof \App\Entity\Article) {
            $this->articleService->removeImage($entity);
        }
    }
}
