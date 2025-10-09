<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Blog>
 *
 * @method Blog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blog[]    findAll()
 * @method Blog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    /**
     * Returns a list of projects - The listing Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('b');
        if ($search) {
            $qb->where('b.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('b.id', 'desc');
        return $qb->getQuery();
    }

    /**
     * @return Blog[]
     */
    public function getSearchResults(string $term, bool $limit = true): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.name LIKE :term')
            ->setParameter('term', '%' . $term . '%');

        if ($limit) {
            $qb->setMaxResults(5);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns projects filtered by a specific category. Frontend Blog display.
     *
     * @param int $categoryId
     * @return Blog[]
     */
    public function getProjectsByCategory(int $categoryId): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.blogCategories', 'bc')
            ->addSelect('bc')
            ->where('bc.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('b.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find the previous portfolio (higher ID) for pagination
     *
     * @param int $currentId
     * @return Blog|null
     */
    public function findPreviousBlog(int $currentId): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->where('b.id > :currentId')
            ->setParameter('currentId', $currentId)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find the next portfolio (lower ID) for pagination
     *
     * @param int $currentId
     * @return Blog|null
     */
    public function findNextBlog(int $currentId): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->where('b.id < :currentId')
            ->setParameter('currentId', $currentId)
            ->orderBy('b.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns all projects ordered by ID in descending order (latest first)
     *
     * @return Blog[]
     */
    public function findAllOrderedDesc(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
