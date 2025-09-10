<?php

namespace App\EventListener;

use App\Services\StudentService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class StudentListener
{
    public function __construct(private StudentService $studentService) {}

    public function postRemove(LifecycleEventArgs $arg)
    {
        $entity = $arg->getObject();
        if ($entity instanceof \App\Entity\Student) {
            $this->studentService->removeImage($entity);
        }
    }
}
