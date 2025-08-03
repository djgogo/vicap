<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRecipientRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/notifications')]
#[IsGranted(User::ROLE_ADMIN)]
class NotificationController extends AbstractController
{
    #[Route('/dropdown', name: 'notification_dropdown', methods: ['GET'])]
    public function dropdown(
        #[CurrentUser] User $user,
        NotificationRecipientRepository $notificationRecipientRepository)
    : Response {
        // Fetch all notifications (pivot records) for this user, ordered by creation date.
        $allNotificationRecipients = $notificationRecipientRepository->findByUserOrdered($user);

        // Separate into unread and read notifications.
        $notificationsUnread = [];
        $notificationsRead = [];

        foreach ($allNotificationRecipients as $nr) {
            if ($nr->getReadAt() === null) {
                $notificationsUnread[] = $nr;
            } else {
                $notificationsRead[] = $nr;
            }
        }

        return $this->render('partials/notification.html.twig', [
            'notifications_all'    => $allNotificationRecipients,
            'notifications_unread' => $notificationsUnread,
            'notifications_read'   => $notificationsRead,
            'unread_count'         => count($notificationsUnread),
        ]);
    }

    #[Route('/mark-as-read/{notificationId}', name: 'admin_notification_mark_as_read', methods: ['GET', 'POST'])]
    public function markAsRead(
        int $notificationId,
        #[CurrentUser] User $user,
        NotificationRecipientRepository $nrRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): RedirectResponse {
        // Retrieve the Notification entity (or handle if not found)
        $notification = $entityManager->getRepository(Notification::class)->find($notificationId);
        if (!$notification) {
            throw $this->createNotFoundException('Notification not found');
        }

        // Retrieve the NotificationRecipient pivot entity for the current user
        $notificationRecipient = $nrRepository->findOneBy([
            'notification' => $notification,
            'recipient'    => $user
        ]);
        if (!$notificationRecipient) {
            throw $this->createNotFoundException('Notification association not found');
        }

        // Mark as read if not already done
        if ($notificationRecipient->getReadAt() === null) {
            $notificationRecipient->setReadAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        // Redirect to the target URL
        return $this->redirect($notification->getUrl());
    }

    #[Route('/clear-all-read', name: 'admin_notification_clear_all_read', methods: ['POST'])]
    public function clearReadNotifications(
        #[CurrentUser] ?User $user,
        NotificationRepository $notificationRepository,
        NotificationRecipientRepository $notificationRecipientRepository,
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        // delete the recipient in the pivot table
        $notificationRecipientRepository->clearAllReadByUser($user);

        // delete all abandoned notifications
        $notificationRepository->clearAllAbandoned();

        return new JsonResponse(['success' => true,]);
    }

    #[Route('/clear-all', name: 'admin_notification_clear_all', methods: ['POST'])]
    public function clearAllNotifications(
        #[CurrentUser] ?User $user,
        NotificationRepository $notificationRepository,
        NotificationRecipientRepository $notificationRecipientRepository,
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        // delete the recipient in the pivot table
        $notificationRecipientRepository->clearAllByUser($user);

        // delete all abandoned notifications
        $notificationRepository->clearAllAbandoned();

        return new JsonResponse(['success' => true,]);
    }
}
