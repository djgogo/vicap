<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Increments or resets user login attempts.
 *
 * @package App\EventSubscriber
 */
class LoginAttemptsSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onLoginSuccess', 3],
            CheckPassportEvent::class => 'onLoginFailure',
        ];
    }

    /**
     * Clears ban time and login attempts.
     */
    public function onLoginSuccess(InteractiveLoginEvent $event)
    {
        if (!$this->params->get('app.auth.enable_throttling')) {
            return;
        }

        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $user->resetLoginAttempts();
        $this->entityManager->flush();
        return true;
    }

    /**
     * Logs user's failed attempts.
     */
    public function onLoginFailure(CheckPassportEvent $event)
    {
        if (!$this->params->get('app.auth.enable_throttling')) {
            return;
        }

        $passport = $event->getPassport();
        $user = $passport->getUser();

        /** @var User $user */
        if (!$user instanceof User) {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $user]);
        }

        // invalid email?
        if (!$user) {
            return;
        }

        $isAlreadySuspended = $user->isLoginSuspended($this->params->get('app.auth.cooldown_seconds'));
        if ($isAlreadySuspended) { // take no action if user login is already disabled
            return;
        }

        $user->incrementLoginAttempts();
        $isOutOfLoginAttempts = $user->isOutOfLoginAttempts($this->params->get('app.auth.max_login_failures'));
        if ($isOutOfLoginAttempts) { // suspend only if user is not suspended
            $user->suspendLogin();
        }
        $this->entityManager->flush();
    }
}
