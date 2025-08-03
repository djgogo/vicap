<?php

namespace App\Event;


use App\Entity\Security\PasswordResetCode;
use App\Entity\User;

/**
 * Emitted when user has requested a password reset.
 * @package App\Event
 */
class PasswordResetEvent extends UserEvent
{
    private PasswordResetCode $code;

    public function __construct(User $user, PasswordResetCode $code)
    {
        parent::__construct($user);
        $this->code = $code;
    }

    public function getCode(): PasswordResetCode
    {
        return $this->code;
    }

}