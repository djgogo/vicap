<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles 404 Not Found exceptions by displaying a custom error page.
 */
class NotFoundExceptionSubscriber implements EventSubscriberInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Check if we're dealing with a 404 Not Found exception
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        // Render the custom 404 error template
        $content = $this->twig->render('public/errors/404.html.twig');

        // Create a new response with the rendered template
        $response = new Response($content, Response::HTTP_NOT_FOUND);

        // Set the response for the event
        $event->setResponse($response);
    }
}