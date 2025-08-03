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
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * This custom Doctrine repository is empty because so far we don't need any custom
 * method to query for application user information. But it's always a good practice
 * to define a custom repository that will be used when the application grows.
 *
 * See https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * @method User|null findOneByUsername(string $username)
 * @method User|null findOneByEmail(string $email)
 *
 * @template-extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($username): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string|null $search
     * @return Query
     */
    public function getListQuery(string $search = null): Query
    {
        $qb = $this->createQueryBuilder('u');
        if ($search) {
            $qb->where('u.email LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('u.id', 'desc');
        return $qb->getQuery();
    }

    /**
     * Returns a number of registration in the given interval.
     */
    public function getRegistrationsCount(DateTime $startDate, ?DateTime $endDate = null)
    {
        if (!$endDate) {
            $endDate = $startDate;
        }

        $minTime = $startDate->format('Y-m-d 00:00:00');
        $maxTime = $endDate->format('Y-m-d 23:59:59');

        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id) AS total')
            ->where('u.created >= :start')
            ->andWhere('u.created <= :end')
            ->setParameter('start', $minTime)
            ->setParameter('end', $maxTime)
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns a number of users online.
     */
    public function getOnlineUsersCount(): mixed
    {
        $interval = new DateInterval(sprintf('PT%dM', User::ONLINE_INTERVAL));
        $minTime = (new DateTime)->sub($interval);
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id) AS total')
            ->where('u.lastSeen >= :mintime')
            ->setParameter('mintime', $minTime)
            ->getQuery()->getSingleScalarResult();
    }

    public function getRegistrationCountPerMonth()
    {
        $minDate = (new DateTime)->sub(new DateInterval('P1Y'));
        $rows = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) AS count')
            ->addSelect("DATE_FORMAT(u.created, '%Y-%m') AS month")
            ->groupBy('month')
            ->where('u.created >= :mindate')
            ->setParameter('mindate', $minDate)
            ->getQuery()
            ->getScalarResult();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['month']] = $row['count'];
        }
        return $result;
    }

    public function getLatestUsers(int $limit = 10)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.created', 'desc')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function getAdminUsers(bool $isFrontend = false): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode('ROLE_ADMIN'));

        if ($isFrontend) {
            $qb->andWhere('u.isFrontEnd = true');
        }

        return $qb->getQuery()->getResult();
    }

    public function getEmployeeUsers(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode('ROLE_EMPLOYEE'));
    }

    public function findAllRegisteredUsersByCourse(int $courseId): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.courseRegistrations', 'cr')
            ->innerJoin('cr.course', 'c')
            ->where('c.id = :courseId')
            ->setParameter('courseId', $courseId)
            ->orderBy('cr.id', 'desc')
            ->getQuery()
            ->getResult();
    }
}
