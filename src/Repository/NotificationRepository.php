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

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Returns all notifications for the given user.
     *
     * @param User $user
     * @return Notification[]
     */
    public function getAllNotificationsByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->innerJoin('n.recipients', 'nr')
            ->addSelect('nr') // This loads the NotificationRecipient data, including readAt!
            ->andWhere('nr.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('n.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Deletes notifications that have no associated recipient records.
     * Typically, these are notifications that were sent to only one user and that user cleared them.
     */
    public function clearAllAbandoned(): void
    {
        $this->getEntityManager()->createQuery(
            'DELETE FROM App\Entity\Notification n
             WHERE n.id NOT IN (
                 SELECT IDENTITY(nr.notification)
                 FROM App\Entity\NotificationRecipient nr
             )'
        )->execute();
    }
}
