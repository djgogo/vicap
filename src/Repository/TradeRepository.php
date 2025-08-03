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

use App\Entity\Trade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Trade>
 *
 * @method Trade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trade[]    findAll()
 * @method Trade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

    /**
     * Returns a list of trades - The listing Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('t');
        if ($search) {
            $qb->where('t.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('t.id', 'desc');
        return $qb->getQuery();
    }

    /**
     * @return Trade[]
     */
    public function getSearchResults(string $term, bool $limit = true): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.name LIKE :term')
            ->setParameter('term', '%' . $term . '%');

        if ($limit) {
            $qb->setMaxResults(5);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns team trades filtered by a specific category. Frontend Trade display.
     *
     * @param int $categoryId
     * @return Trade[]
     */
    public function getTradesByCategory(int $categoryId): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.tradeCategory', 'tc')
            ->addSelect('tc')
            ->where('tc.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('t.id', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
