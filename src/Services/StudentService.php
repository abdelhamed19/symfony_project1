<?php

namespace App\Services;

use App\Entity\Student;
use App\Kernel;
use App\Repository\StudentRepository;
use App\Traits\FileTrait;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudentService
{
    public function __construct(
        private StudentRepository $studentRepository,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($request)
    {
        $data = $this->studentRepository->findAll();
        return $this->paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }
    public function showStudent($id)
    {
        return $this->studentRepository->find($id);
    }
    public function createStudent($data, $student)
    {
        try {
            if (isset($data['image'])) {
                // upload file
                $file = $this->uploadFile($data['image']);
                $student->setImage($file);
            }
            $this->entityManager->persist($student);
            $this->entityManager->flush();
            return $student;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    private function uploadFile(UploadedFile $file)
    {
        $projectDir = $this->kernel->getProjectDir();
        $fileDir = $projectDir . '/' . 'public/uploads/students';
        $fileName = $file->getClientOriginalName() . '-' . rand(100, 9999);
        try {
            $file->move($fileDir, $fileName);
            return $fileName;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
