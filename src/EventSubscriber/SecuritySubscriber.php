<?php

namespace App\EventSubscriber;


use App\Event\ChangePasswordEvent;
use App\Event\PasswordResetEvent;
use App\Event\SecurityEvents;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens to security events.
 *
 * @package App\EventSubscriber
 */
class SecuritySubscriber implements EventSubscriberInterface
{
    /**
     * SecuritySubscriber constructor.
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $generator,
        public EmailService $email,
    ){
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::PASSWORD_RESET_REQUEST => 'onPasswordResetRequested',
//            SecurityEvents::TWO_FACTOR_EMAIL_SEND => 'onTwoFactorEmailCodeRequested',
            SecurityEvents::PASSWORD_CHANGED => 'onPasswordChanged',
        ];
    }

    /**
     * Sends the reset password link email to user.
     */
    public function onPasswordResetRequested(PasswordResetEvent $event): void
    {
        $user = $event->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        // generate password reset absolute URL
        $confirmationUrl = $this->generator->generate(
            'security-password-change',
            ['code' => $event->getCode()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // send password reset email to user
        $this->email->sendEmail(
            templateName:'user_password_reset',
            locale: $locale,
            placeholders: [
                '%%USER_FIRSTNAME%%' => $user->getFirstName(),
                '%%PASSWORD_RESET_URL%%' => $confirmationUrl,
            ],
            toEmail: $user->getEmail()
        );
    }

//    /**
//     * @param TwoStepEmailCodeEvent $event
//     */
//    public function onTwoFactorEmailCodeRequested(TwoStepEmailCodeEvent $event)
//    {
//        $this->sendEmail(
//            $event->getUser(),
//            $this->translator->trans('mail.2fa_code.subject'),
//            'auth/security/mails/2f_mail_code.html.twig',
//            ['code' => $event->getCode()]
//        );
//    }


    public function onPasswordChanged(ChangePasswordEvent $event)
    {
        $user = $event->getUser();
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        // send password has been changed email
        $this->email->sendEmail(
            templateName:'user_password_changed',
            locale: $locale,
            placeholders: [
                '%%USER_FIRSTNAME%%' => $user->getFirstName(),
            ],
            toEmail: $user->getEmail()
        );
    }

}