<?php

namespace App\Services;

use App\Kernel;
use App\Entity\Student;
use App\Traits\FileTrait;
use Symfony\Component\Uid\Uuid;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudentService
{
    public function __construct(
        private StudentRepository $studentRepository,
        private EntityManagerInterface $em,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($page)
    {
        $qb = $this->em
            ->createQueryBuilder()
            ->select('s', 'c')
            ->from(Student::class, 's')
            ->leftJoin('s.courses', 'c');

        $query = $qb->getQuery();


        return $this->paginator->paginate(
            $query,
            $page,
            20
        );
    }
    private function getUploadPath(): string
    {
        return "{$this->kernel->getProjectDir()}/public/uploads/students";
    }

    public function uploadImage(Student $student, $uploadedFile): void
    {
        if ($uploadedFile instanceof UploadedFile) {
            $this->removeImage($student);
            $fileName = Uuid::v4();
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if ($extension) {
                $fileName = "{$fileName}.{$extension}";
            }
            $uploadedFile->move($this->getUploadPath(), $fileName);

            $student->setImage($fileName);

            $this->em->flush();
        }
    }

    public function removeImage(Student $student): void
    {
        if ($student->getImage()) {
            $filePath = "{$this->getUploadPath()}/{$student->getImage()}";
            $fs = new Filesystem();
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
            }
        }

        if ($student->getId()) {
            $student->setImage(null);
            $this->em->flush();
        }
    }
}
