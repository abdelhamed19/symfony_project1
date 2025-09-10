<?php

namespace App\Services;

use App\Kernel;
use App\Entity\Course;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class CourseService
{
    public function __construct(
        private CourseRepository $courseRepository,
        private EntityManagerInterface $em,
        private PaginatorInterface $paginator,
        private Kernel $kernel
    ) {}

    public function listAll($page)
    {
        $db = $this->em->createQueryBuilder()
            ->select('c', 's')
            ->from(Course::class, 'c')
            ->leftJoin('c.students', 's');

        $query = $db->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            20
        );
    }
}
