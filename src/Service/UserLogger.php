<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLog;
use Doctrine\ORM\EntityManagerInterface;

class UserLogger
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function log(User $user, string $action = ''): void
    {
        $log = new UserLog();
        $log->setUser($user);
        $log->setAction($action);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}