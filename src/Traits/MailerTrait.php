<?php

namespace App\Traits;

use App\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

trait MailerTrait
{
    /**
     * @param User $user
     * @param string $subject
     * @param string $template
     * @param array $context
     * @throws TransportExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function sendEmail(User $user, string $subject, string $template, $context = [])
    {
        $context = array_merge(['user' => $user], $context);
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('gogo@websitemaster.ch')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            //->text('Sending emails is fun again!')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}