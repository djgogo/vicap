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

use App\Entity\Portfolio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Portfolio>
 *
 * @method Portfolio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Portfolio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Portfolio[]    findAll()
 * @method Portfolio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortfolioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Portfolio::class);
    }

    /**
     * Returns a list of projects - The listing Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('p');
        if ($search) {
            $qb->where('p.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('p.id', 'desc');
        return $qb->getQuery();
    }

    /**
     * @return Portfolio[]
     */
    public function getSearchResults(string $term, bool $limit = true): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.name LIKE :term')
            ->setParameter('term', '%' . $term . '%');

        if ($limit) {
            $qb->setMaxResults(5);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns team trades filtered by a specific category. Frontend Portfolio display.
     *
     * @param int $categoryId
     * @return Portfolio[]
     */
    public function getProjectsByCategory(int $categoryId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.portfolioCategory', 'pc')
            ->addSelect('pc')
            ->where('pc.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
