<?php

namespace App\EventListener;

use App\Services\CategoryService;
use App\Traits\FileTrait;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CategoryListner
{
    public function __construct(private CategoryService $categoryService) {}
    public function postRemove(LifecycleEventArgs $arg)
    {
        $entity = $arg->getObject();
        if ($entity instanceof \App\Entity\Category) {
            $this->categoryService->removeImage($entity);
        }
    }
}
