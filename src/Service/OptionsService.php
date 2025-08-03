<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\UserOption;
use Doctrine\ORM\EntityManagerInterface;

class OptionsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function isEmailEnabledForUser(User $user): bool
    {
        $emailOption = $this->entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $user,
            'name' => 'email_notifications_enabled',
        ]);

        if (!$emailOption) {
            return true;
        }

        return $emailOption->isValue() ?: false;
    }

    public function isNotificationEnabledForUser(User $user): bool
    {
        $notificationOption = $this->entityManager->getRepository(UserOption::class)->findOneBy([
            'user' => $user,
            'name' => 'notifications_enabled',
        ]);

        if (!$notificationOption) {
            return true;
        }

        return $notificationOption->isValue() ?: false;
    }

}
