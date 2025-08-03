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

use App\Entity\NotificationRecipient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationRecipient::class);
    }

    /**
     * Returns all NotificationRecipient records for the given user ordered by the notification's creation date (DESC).
     *
     * @param User $user
     * @return NotificationRecipient[]
     */
    public function findByUserOrdered(User $user): array
    {
        return $this->createQueryBuilder('nr')
            ->join('nr.notification', 'n')
            ->andWhere('nr.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('n.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function clearAllReadByUser(User $user): void
    {
        $this->createQueryBuilder('nr')
            ->delete(NotificationRecipient::class, 'nr')
            ->where('nr.recipient = :user')
            ->andWhere('nr.readAt IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()->execute();
    }

    public function clearAllByUser(User $user): void
    {
        $this->createQueryBuilder('nr')
            ->delete(NotificationRecipient::class, 'nr')
            ->where('nr.recipient = :user')
            ->setParameter('user', $user)
            ->getQuery()->execute();
    }
}