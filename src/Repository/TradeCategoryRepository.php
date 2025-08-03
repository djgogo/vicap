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

use App\Entity\TradeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<TradeCategory>
 *
 * @method TradeCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TradeCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TradeCategory[]    findAll()
 * @method TradeCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TradeCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeCategory::class);
    }

    /**
     * @param string|null $search
     * @return Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('tc')
            ->select('tc, COUNT(t.id) AS tradeCount')
            ->leftJoin('tc.trades', 't');

        if ($search) {
            $qb->where('tc.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->groupBy('tc.id')
            ->orderBy('tc.id', 'desc');

        return $qb->getQuery();
    }

}
