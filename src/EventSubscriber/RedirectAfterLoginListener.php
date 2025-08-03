<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class RedirectAfterLoginListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        $user = $event->getPassport()->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $response = new RedirectResponse($this->router->generate('admin_dashboard_index'));
//        } elseif (in_array('ROLE_USER', $user->getRoles(), true)) {
//            $response = new RedirectResponse($this->router->generate('user_dashboard_index'));
        } else {
            return;  // Do nothing for other roles or unauthenticated users
        }

        $event->setResponse($response);
    }
}