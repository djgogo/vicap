<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class AuthService
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Checks if the given user is the currently logged-in user.
     */
    public function isLoggedIn(User $user): bool
    {
        // Retrieve the current security token
        $token = $this->tokenStorage->getToken();

        // If no token is available, nobody is logged in
        if (null === $token) {
            return false;
        }

        // Get the user object from the token
        $loggedInUser = $token->getUser();

        // Ensure the token's user is an actual User entity and compare
        if ($loggedInUser instanceof User && $loggedInUser->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}