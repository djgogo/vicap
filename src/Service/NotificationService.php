<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\Notification;
use App\Entity\NotificationRecipient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $router
    ) {
    }

    /**
     * Creates and persists a notification for one or more recipients.
     *
     * @param string   $messageKey The translation key (e.g., "notification.candidate.course_registration")
     * @param array    $parameters Parameters for the translation (e.g., ['%candidateName%' => 'John Doe', '%courseTitle%' => 'Symfony 101'])
     * @param string|null $type Optional type of notification (e.g., "candidate")
     * @param string|null $url  URL or route to redirect to when the notification is clicked
     * @param User[]   $recipients Array of User objects that should receive the notification
     *
     * @return Notification The created Notification entity
     */
    public function sendNotification(
        string $messageKey,
        array $parameters,
        ?string $type,
        ?string $route,
        ?array $routeParameters,
        ?int $senderId,
        array $recipients
    ): Notification {
        // create url form the given route parameter
        $url = $this->router->generate($route, $routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);

        // Create the Notification entity with the given data.
        $notification = new Notification();
        $notification->setMessageKey($messageKey)
            ->setParameters($parameters)
            ->setType($type)
            ->setUrl($url)
            ->setSender($senderId);

        $this->entityManager->persist($notification);

        // Loop through each recipient and create a NotificationRecipient entry.
        foreach ($recipients as $recipient) {
            $notificationRecipient = new NotificationRecipient();
            $notificationRecipient->setNotification($notification);
            $notificationRecipient->setRecipient($recipient);
            // Initially, readAt remains null (meaning unread).

            $this->entityManager->persist($notificationRecipient);
            $notification->addRecipient($notificationRecipient);
        }

        // Flush once to persist both Notification and NotificationRecipient records.
        $this->entityManager->flush();

        return $notification;
    }
}