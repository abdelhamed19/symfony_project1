<?php

namespace App\Services;

use App\Kernel;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CourseService
{
    public function __construct(
        private CourseRepository $courseRepository,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($request)
    {
        $data = $this->courseRepository->findAll();
        return $this->paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }
    public function showCourse($id)
    {
        return $this->courseRepository->find($id);
    }
    public function createCourse($course)
    {
        try {
            $this->entityManager->persist($course);
            $this->entityManager->flush();
            return $course;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
