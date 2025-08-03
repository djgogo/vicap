<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SimpleMathCaptcha
{
    private SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        // Retrieve the session from the current request. Symfony will create a session when needed, even for anonymous users.
        $session = $requestStack->getSession();
        $this->session = $session;
    }

    /**
     * Generates a math captcha question and stores the correct result in the session.
     *
     * @return string The math question to display.
     */
    public function generateCaptcha(): string
    {
        // Generate two random numbers between 1 and 10.
        $num1 = random_int(1, 10);
        $num2 = random_int(1, 10);

        // Use addition for the captcha.
        $result = $num1 + $num2;

        // Save the result in the session.
        $this->session->set('captcha_result', $result);

        // Return the question to display in the form.
        return sprintf('Recpatcha: %d + %d?', $num1, $num2);
    }

    /**
     * Validates the user's answer against the stored result.
     *
     * @param mixed $userAnswer The answer provided by the user.
     * @return bool True if the answer is correct, false otherwise.
     */
    public function isValid($userAnswer): bool
    {
        // Retrieve the expected result from the session.
        $expected = $this->session->get('captcha_result');

        // Compare the user's answer to the expected result.
        if ((int)$userAnswer === (int)$expected) {
            // Remove the stored result only on success.
            $this->session->remove('captcha_result');
            return true;
        }

        return false;
    }
}