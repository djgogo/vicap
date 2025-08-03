<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -30]
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if (!$user->isEmailConfirmed()) {
            throw new CustomUserMessageAuthenticationException(
                'Please verify your email before logging in.'
            );
        }
    }
}