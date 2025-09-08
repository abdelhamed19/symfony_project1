<?php

namespace App\EventListener;

use App\Services\CategoryService;
use App\Traits\FileTrait;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CategoryListner
{
    use FileTrait;
    public function __construct(private CategoryService $categoryService) {}
    public function postRemove(LifecycleEventArgs $arg)
    {
        $entity = $arg->getObject();
        if ($entity instanceof \App\Entity\Category) {
            $entity->deleteImage(\App\Entity\Category::IMAGE_DIR, 'image');
        }
    }
}
