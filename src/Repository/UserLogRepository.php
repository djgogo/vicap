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

use App\Entity\User;
use App\Entity\UserLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLog>
 *
 * @method UserLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLog[]    findAll()
 * @method UserLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLog::class);
    }

    /**
     * @return UserLog[] Returns an array of UserLog objects
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->orderBy('u.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByUserQuery(User $user): Query
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->orderBy('u.created', 'DESC')
            ->getQuery()
            ;
    }

    /**
     * @return UserLog[] Returns an array of UserLog objects for the recent Activity in the profile - only the last 5 entries
     */
    public function getRecentActivityByUser(User $user): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.user = :user')
            ->setParameter('user', $user)
            ->orderBy('u.created', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?UserLog
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
