<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }
    public function findByName(string $name): ?Category
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findMaxSortOrder(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('MAX(c.sort_order)')
            ->getQuery()
            ->getSingleScalarResult();

        return $result === null ? 0 : (int) $result;
    }

    public function findAllOrderedBySortOrder($sort)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sort_order', $sort)
            ->getQuery()
            ->getResult();
    }
}
