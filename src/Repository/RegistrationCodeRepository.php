<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @package App\Repository
 */
class RegistrationCodeRepository extends EntityRepository
{
    public function deleteByUser(User $user)
    {
        return $this->createQueryBuilder('rc')
            ->delete('App\Entity\Security\RegistrationCode', 'rc')
            ->where('rc.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
