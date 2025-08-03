<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Index(name: "key_idx", columns: ["message_key"])]
#[ORM\Index(name: "type_idx", columns: ["type"])]
#[ORM\Index(name: "created_idx", columns: ["created"])]
#[ORM\Table(name: "notifications")]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // The translation key used to retrieve the localized message.
    #[ORM\Column(length: 255)]
    private string $messageKey;

    // Dynamic parameters for the translation (stored as JSON).
    #[ORM\Column(type: "json", nullable: true)]
    private ?array $parameters = [];

    // Optional type/context (e.g., "job", "registration", "project", etc.).
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    // The URL or route that the notification should redirect to.
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    // When the notification was created.
    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeInterface $created;

    #[ORM\ManyToOne]
    private ?User $sender = null;

    // One-to-many relation to the pivot entity. One notification may have many recipients.
    #[ORM\OneToMany(targetEntity: NotificationRecipient::class, mappedBy: "notification", cascade: ["persist", "remove"])]
    private Collection $recipients;

    public function __construct()
    {
        $this->created = new \DateTimeImmutable();
        $this->recipients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function setMessageKey(string $messageKey): self
    {
        $this->messageKey = $messageKey;
        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return Collection<int, NotificationRecipient>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(NotificationRecipient $notificationRecipient): self
    {
        if (!$this->recipients->contains($notificationRecipient)) {
            $this->recipients[] = $notificationRecipient;
            $notificationRecipient->setNotification($this);
        }

        return $this;
    }

    public function removeRecipient(NotificationRecipient $notificationRecipient): self
    {
        if ($this->recipients->removeElement($notificationRecipient)) {
            // Set the owning side to null if necessary
            if ($notificationRecipient->getNotification() === $this) {
                $notificationRecipient->setNotification(null);
            }
        }

        return $this;
    }

    /**
     * Retrieve the translated notification based on the user's locale.
     */
    public function getTranslatedMessage(TranslatorInterface $translator, string $locale): string
    {
        return $translator->trans($this->messageKey, $this->parameters ?? [], 'notifications', $locale);
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }
}