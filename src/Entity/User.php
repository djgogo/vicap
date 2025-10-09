<?php

namespace App\Entity;

use App\Entity\User\LocationTrait;
use App\Entity\User\ProfileTrait;
use App\Entity\User\ThrottlingInfoTrait;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Defines the properties of the User entity to represent the application users.
 * See https://symfony.com/doc/current/doctrine.html#creating-an-entity-class.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use ProfileTrait;
    use LocationTrait;
    use ThrottlingInfoTrait;

    /**
     * The constant defines a period since the user's last activity during which the user is considered to be online.
     * The value is in minutes.
     */
    const ONLINE_INTERVAL = 3;

    // We can use constants for roles to find usages all over the application rather
    // than doing a full-text search on the "ROLE_" string.
    // It also prevents from making typo errors.
    final public const ROLE_USER = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE)]
    private \DateTime $created;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE)]
    private \DateTime $updated;

    #[ORM\Column(name: 'last_seen', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastSeen;

    #[ORM\Column(name: 'last_logged_in', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastLoggedIn;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    #[Assert\NotBlank(groups: ["registration", "user_create"])]
    #[Assert\Length(max: 128, groups: ["registration", "user_create"])]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\Length(min: 8, max: 64)]
    private string $password;

    /**
     * Non persisted field.
     *
     * The field temporarily stores the plain password from the registration form.
     * This field can be validated and is then used to populate the password field.
     */
    #[Assert\Length(min: 8, max: 4096, groups: ["registration", "reset_password", "user_create", "change_password"])]
    #[Assert\NotBlank(groups: ["registration", "reset_password", "user_create", "change_password"])]
    private ?string $plainPassword = '';

    #[ORM\Column(name: 'is_super_admin', type: Types::BOOLEAN)]
    private bool $isSuperAdmin = false;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(name: 'email_confirmed', type: Types::BOOLEAN)]
    private bool $isEmailConfirmed = false;

    #[ORM\Column(name: 'is_frontend', type: Types::BOOLEAN)]
    private bool $isFrontEnd = false;

    #[ORM\Column(name: 'locale', type: Types::TEXT, nullable: true)]
    private ?string $locale = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isOnboardingCompleted = false;

    #[ORM\OneToMany(targetEntity: UserLog::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userLogs;

    #[ORM\OneToMany(targetEntity: UserOption::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userOptions;

    // One-to-many relation to the pivot entity. One user may have many notifications.
    #[ORM\OneToMany(targetEntity: NotificationRecipient::class, mappedBy: "recipient", cascade: ["persist", "remove"])]
    private Collection $notifications;

    #[ORM\OneToMany(targetEntity: Blog::class, mappedBy: 'author', cascade: ["persist", "remove"])]
    private Collection $blogs;

    public function __construct(string $email = '', string $hashedPassword = '')
    {
        $this->email = $email;
        $this->password = $hashedPassword;
        $this->created = new DateTime;
        $this->updated = new DateTime;
        $this->lastSeen = new DateTime;
        $this->userLogs = new ArrayCollection();
        $this->userOptions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->blogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    public function getLastSeen(): ?DateTime
    {
        return $this->lastSeen;
    }

    public function setLastSeen(?DateTime $lastSeen): User
    {
        $this->lastSeen = $lastSeen;
        return $this;
    }

    public function getLastLoggedIn(): ?DateTime
    {
        return $this->lastLoggedIn;
    }

    public function setLastLoggedIn(?DateTime $lastLoggedIn): User
    {
        $this->lastLoggedIn = $lastLoggedIn;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getEmail();
    }

    public function getUsername(): ?string
    {
        return $this->getEmail();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function isSuperAdmin(): ?bool
    {
        return $this->isSuperAdmin;
    }

    public function isAdmin(): ?bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function setIsSuperAdmin(bool $isSuperAdmin): void
    {
        $this->isSuperAdmin = $isSuperAdmin;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): User
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isEmailConfirmed(): ?bool
    {
        return $this->isEmailConfirmed;
    }

    public function setIsEmailConfirmed(bool $isEmailConfirmed): User
    {
        $this->isEmailConfirmed = $isEmailConfirmed;
        return $this;
    }

    public function isFrontEnd(): ?bool
    {
        return $this->isFrontEnd;
    }

    public function setIsFrontEnd(bool $isFrontEnd): User
    {
        $this->isFrontEnd = $isFrontEnd;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?? 'en';
    }

    public function setLocale(?string $locale): User
    {
        $this->locale = $locale;
        return $this;
    }

    public function getCompleteAddress(): string
    {
        return $this->displayName() . ', ' . $this->getAddress() . ', ' . $this->getZip() . ' ' . $this->getCity() . ', ' . $this->getCountry();
    }

    public function getAllUserRoles(): array
    {
        return [
            'ROLE_USER' => 'ROLE_USER',
            'ROLE_ADMIN' => 'ROLE_ADMIN',
        ];
    }

    public function getAdminIndexUserRoles(): array
    {
        return [
            'ROLE_USER' => 'ROLE_USER',
            'ROLE_ADMIN' => 'ROLE_ADMIN',
        ];
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = self::ROLE_USER;
        }

        return array_unique($roles);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }


    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // We're using bcrypt in security.yaml to encode the password, so
        // the salt value is built-in and you don't have to generate one
        // See https://en.wikipedia.org/wiki/Bcrypt

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        $this->plainPassword = null;
    }

    /**
     * Tests if user is online (seen in the last ONLINE_INTERVAL minutes).
     */
    public function isOnline(): bool
    {
        if (!$this->lastSeen) { // never logged in
            return false;
        }
        $currentTs = (new DateTime)->getTimestamp();
        $lastSeenTs = $this->lastSeen->getTimestamp();
        return ($currentTs - $lastSeenTs) < (3 * 60);
    }

    public function __toString()
    {
        return (string)$this->displayName();
    }

    public function isOnboardingCompleted(): bool
    {
        return $this->isOnboardingCompleted;
    }

    public function setIsOnboardingCompleted(bool $isOnboardingCompleted): static
    {
        $this->isOnboardingCompleted = $isOnboardingCompleted;

        return $this;
    }

    /**
     * @return Collection<int, UserLog>
     */
    public function getUserLogs(): Collection
    {
        return $this->userLogs;
    }

    public function addUserLog(UserLog $userLog): static
    {
        if (!$this->userLogs->contains($userLog)) {
            $this->userLogs->add($userLog);
            $userLog->setUser($this);
        }

        return $this;
    }

    public function removeUserLog(UserLog $userLog): static
    {
        if ($this->userLogs->removeElement($userLog)) {
            // set the owning side to null (unless already changed)
            if ($userLog->getUser() === $this) {
                $userLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserOption>
     */
    public function getUserOptions(): Collection
    {
        return $this->userOptions;
    }

    public function addUserOption(UserOption $userOption): static
    {
        if (!$this->userOptions->contains($userOption)) {
            $this->userOptions->add($userOption);
            $userOption->setUser($this);
        }

        return $this;
    }

    public function removeUserOption(UserOption $userOption): static
    {
        if ($this->userOptions->removeElement($userOption)) {
            // set the owning side to null (unless already changed)
            if ($userOption->getUser() === $this) {
                $userOption->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, NotificationRecipient>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(NotificationRecipient $notificationRecipient): self
    {
        if (!$this->notifications->contains($notificationRecipient)) {
            $this->notifications[] = $notificationRecipient;
            $notificationRecipient->setRecipient($this);
        }

        return $this;
    }

    public function removeNotification(NotificationRecipient $notificationRecipient): self
    {
        if ($this->notifications->removeElement($notificationRecipient)) {
            // Set the owning side to null if necessary
            if ($notificationRecipient->getRecipient() === $this) {
                $notificationRecipient->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Notification[]
     */
    public function getAllNotifications(): array
    {
        return array_map(
            fn(NotificationRecipient $nr) => $nr->getNotification(),
            $this->notifications->toArray()
        );
    }

    /**
     * @return Collection<int, Blog>
     */
    public function getBlogs(): Collection
    {
        return $this->blogs;
    }

    public function addBlog(Blog $blog): static
    {
        if (!$this->blogs->contains($blog)) {
            $this->blogs->add($blog);
            $blog->setAuthor($this);
        }

        return $this;
    }

    public function removeBlog(Blog $blog): static
    {
        if ($this->blogs->removeElement($blog)) {
            // set the owning side to null (unless already changed)
            if ($blog->getAuthor() === $this) {
                $blog->setAuthor(null);
            }
        }

        return $this;
    }

}
