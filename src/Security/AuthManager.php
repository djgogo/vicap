<?php

namespace App\Security;

use App\Entity\Security\PasswordResetCode;
use App\Entity\Security\RegistrationCode;
use App\Entity\User;
use App\Service\UserLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Common operations with User entity.
 * @package App\Security
 */
class AuthManager
{
    protected EntityManagerInterface $entityManager;
    protected UserPasswordHasherInterface $passwordHasher;
    protected UserLogger $userLogger;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserLogger $userLogger
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userLogger = $userLogger;
    }

    /**
     * Load user by email.
     */
    public function load($email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    /**
     * Register a user.
     */
    public function register(User $user, string $jobSlug = null): User
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPlainPassword())
            );
        }

        $action = 'Candidate ' . $user->displayName() . ' registered';
        $this->userLogger->log($user, $action);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $this->entityManager->flush();
    }

    /**
     * Create a new registration code (used to confirm email address).
     */
    public function createRegistrationCode(User $user): RegistrationCode
    {
        $this->entityManager->getRepository(RegistrationCode::class)->deleteByUser($user);
        $regCode = new RegistrationCode($user);
        $this->entityManager->persist($regCode);
        $this->entityManager->flush();
        return $regCode;
    }

    /**
     * Create a password reset code (used to restore password).
     */
    public function createPasswordResetCode(User $user): PasswordResetCode
    {
        $this->entityManager->getRepository(PasswordResetCode::class)->deleteByUser($user);

        $code = new PasswordResetCode($user);
        $this->entityManager->persist($code);
        $this->entityManager->flush();
        return $code;
    }
}