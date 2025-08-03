<?php

namespace App\EventSubscriber;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Logs user last access and last login dates.
 *
 * @package App\EventSubscriber
 */
class UserActivityLoggerSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $entityManager;
    protected TokenStorageInterface $tokenStorage;
    private RouterInterface $router;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onKernelRequestSecurityInteractiveLogin', 2],
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * Logs user's last log in time.
     */
    public function onKernelRequestSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        date_default_timezone_set('Europe/Berlin');

        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $user->setLastLoggedIn(new DateTime);
        $this->entityManager->persist($user);
    }

    /**
     * Logs user's last seen time.
     */
    public function onKernelRequest(RequestEvent $event)
    {
        date_default_timezone_set('Europe/Berlin');

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();

        // e.g. anonymous authentication
        if (!is_object($user)) {
            return;
        }

        $user->setLastSeen(new DateTime);
        $this->entityManager->persist($user);
    }
}
