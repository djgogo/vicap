<?php

namespace App\Event;


use Symfony\Contracts\EventDispatcher\Event;

class SecurityEvents extends Event
{
    const PASSWORD_RESET_REQUEST = 'security.password_reset';
    const PASSWORD_CHANGED = 'security.password_changed';
    const SOCIAL_ACCOUNT_CONNECTED = 'security.social_account_connected';
    const TWO_FACTOR_EMAIL_SEND = 'security.two_factor_email_send';

}