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

use App\Entity\PortfolioCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<PortfolioCategory>
 *
 * @method PortfolioCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PortfolioCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PortfolioCategory[]    findAll()
 * @method PortfolioCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortfolioCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortfolioCategory::class);
    }

    /**
     * @param string|null $search
     * @return Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('pc')
            ->select('pc, COUNT(p.id) AS projectCount')
            ->leftJoin('pc.projects', 'p');

        if ($search) {
            $qb->where('pc.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->groupBy('pc.id')
            ->orderBy('pc.id', 'desc');

        return $qb->getQuery();
    }

}
