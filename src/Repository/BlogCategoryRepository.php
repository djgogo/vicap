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

use App\Entity\BlogCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<BlogCategory>
 *
 * @method BlogCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlogCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlogCategory[]    findAll()
 * @method BlogCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlogCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogCategory::class);
    }

    /**
     * @param string|null $search
     * @return Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('bc')
            ->select('bc, COUNT(p.id) AS blogCount')
            ->leftJoin('bc.blogs', 'p');

        if ($search) {
            $qb->where('bc.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->groupBy('bc.id')
            ->orderBy('bc.id', 'desc');

        return $qb->getQuery();
    }

}
