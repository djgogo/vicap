<?php

namespace App\Repository;


use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class PasswordResetCodeRepository extends EntityRepository
{
    public function deleteByUser(User $user)
    {
        return $this->createQueryBuilder('pr')
            ->delete(\App\Entity\Security\PasswordResetCode::class, 'pr')
            ->where('pr.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

}