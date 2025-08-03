<?php

namespace App\Entity\User;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


/**
 * Enhances user with login attempts information.
 * Keeps track about ban time.
 *
 * @package App\Entity\User
 */
trait ThrottlingInfoTrait
{
    #[ORM\Column(name: 'login_attempts', type: Types::INTEGER)]
    private int $loginAttempts = 0;

    #[ORM\Column(name: 'login_banned_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $loginBannedAt;

    public function getLoginAttempts(): int
    {
        return $this->loginAttempts;
    }

    public function getLoginBannedAt(): ?\DateTimeImmutable
    {
        return $this->loginBannedAt;
    }

    public function setLoginBannedAt(\DateTimeImmutable $loginBannedAt = null)
    {
        $this->loginBannedAt = $loginBannedAt;
        return $this;
    }

    /**
     * Increment login attempts by one.
     */
    public function incrementLoginAttempts()
    {
        $this->loginAttempts++;
    }

    /**
     * Resets information about failed login attempts.
     */
    public function resetLoginAttempts()
    {
        $this->loginAttempts = 0;
        $this->loginBannedAt = null;
    }

    /**
     * Suspend login because of failed login attempts.
     * Set in LoginAttemptsSubscriber.
     */
    public function suspendLogin()
    {
        date_default_timezone_set('Europe/Berlin');

        $this->loginAttempts = 0; // reset failed login counter
        $this->loginBannedAt = new \DateTimeImmutable();
    }

    /**
     * Test if user has used the login attempts limit.
     *
     * @param int $max
     * @return bool
     */
    public function isOutOfLoginAttempts(int $max): bool
    {
        return $this->loginAttempts >= $max;
    }

    /**
     * Test if login is disabled for user because of failed login attempts.
     * @param int $durationSeconds
     * @return bool
     */
    public function isLoginSuspended(int $durationSeconds): bool
    {
        date_default_timezone_set('Europe/Berlin');

        if (!$this->loginBannedAt) {
            return false;
        }

        $diff = (new DateTime(null, new DateTimeZone('Europe/Berlin')))->getTimestamp() - $this->loginBannedAt->getTimestamp();
        return $diff <= $durationSeconds;
    }
}