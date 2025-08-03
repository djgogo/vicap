<?php

namespace App\Security\Exception;


use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooManyFailedAttemptsException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'error.account_suspended_due_to_failed_attempts';
    }
}