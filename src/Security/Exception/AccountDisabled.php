<?php

namespace App\Security\Exception;


use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountDisabled extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'error.account_disabled';
    }
}