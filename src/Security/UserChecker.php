<?php

namespace App\Security;


use App\Entity\User;
use App\Security\Exception\AccountDisabled;
use App\Security\Exception\TooManyFailedAttemptsException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Performs authentication checks on user after login.
 * @package App\Security
 */
class UserChecker implements UserCheckerInterface
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $authThrottlingEnabled = $this->params->get('app.auth.enable_throttling');
        if ($authThrottlingEnabled) {
            // test if user is out of login attempts
            $jailDuration = $this->params->get('app.auth.cooldown_seconds');
            if ($user->isLoginSuspended($jailDuration)) {
                throw new TooManyFailedAttemptsException();
            }
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new AccountDisabled();
        }
    }
}