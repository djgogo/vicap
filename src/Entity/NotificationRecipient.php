<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Entity;

use App\Repository\NotificationRecipientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRecipientRepository::class)]
#[ORM\Table(name: "notification_recipients")]
class NotificationRecipient
{
    // Composite primary key: the notification.
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Notification::class, inversedBy: "recipients")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Notification $notification = null;

    // Composite primary key: the recipient user.
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $recipient = null;

    // When this recipient read the notification (null if unread).
    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeInterface $readAt = null;

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;
        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): self
    {
        $this->readAt = $readAt;
        return $this;
    }
}
