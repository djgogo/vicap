<?php

namespace App\Controller\Auth;

use App\Entity\Candidate;
use App\Entity\Security\RegistrationCode;
use App\Entity\User;
use App\Event\RegistrationEvent;
use App\Form\RegisterForm;
use App\Security\AuthManager;
use App\Service\State\RegisteredState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles registration process.
 *
 * @package App\Controller
 */
class RegistrationController extends AbstractController
{
    private string $recaptchaSiteKey;
    private bool $useRecaptcha;
    private RateLimiterFactory $registrationLimiterFactory;
    private LoggerInterface $logger;
    private ReCaptcha $reCaptcha;

    public function __construct(
        string $googleRecaptchaSiteKey,
        RateLimiterFactory $registrationLimiterFactory,
        LoggerInterface $logger,
        ParameterBagInterface $params,
        ReCaptcha $reCaptcha,
    ) {
        $this->recaptchaSiteKey = $googleRecaptchaSiteKey;
        $this->registrationLimiterFactory = $registrationLimiterFactory;
        $this->logger = $logger;
        $this->useRecaptcha = $params->get('registration.use_recaptcha') ?? false;
        $this->reCaptcha = $reCaptcha;
    }

    #[Route("/registration", name: "security_registration")]
    public function register(
        Request $request,
        AuthManager $authManager,
        EventDispatcherInterface $events,
        SessionInterface $session,
    )
    {
        // redirect to dashboard if already logged in
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        $user = new User;
        $user->setRoles([User::ROLE_USER]);

        // Create the form and enable reCAPTCHA based on the parameter
        $form = $this->createForm(RegisterForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Check honeypot
            $honeypot = $form->get('honeypot')->getData();
            if (!empty($honeypot)) {
                $this->addFlash('danger', 'Invalid submission.');
                return $this->redirectToRoute('security_registration');
            }

            if ($this->useRecaptcha) {
                // Validate reCAPTCHA
                $recaptchaResponse = $request->request->get('g-recaptcha-response');
                $remoteIp = $request->getClientIp();

                // Verify the reCAPTCHA token using the injected service
                $resp = $this->reCaptcha->verify($recaptchaResponse, $remoteIp);

                if ($resp->isSuccess()) {
                    // Enforce rate limiting
                    $limiter = $this->registrationLimiterFactory->create($request->getClientIp());
                    $limit = $limiter->consume();

                    if (!$limit->isAccepted()) {
                        $this->addFlash('danger', 'You have reached the registration limit. Please try again later.');
                        return $this->redirectToRoute('security_registration');
                    }

                    // Dispatch BEFORE_USER_REGISTERED event
                    $events->dispatch(new RegistrationEvent($user, new RegistrationCode($user)), RegistrationEvent::BEFORE_USER_REGISTERED);

                    // Register the user
                    $user = $authManager->register($user);
                    $regCode = $authManager->createRegistrationCode($user);

                    // Dispatch USER_REGISTERED event
                    $events->dispatch(new RegistrationEvent($user, $regCode), RegistrationEvent::USER_REGISTERED);

                    $this->addFlash('success', 'flash_message.registration_successful');
                    return $this->redirectToRoute('security_login');
                } else {
                    // Handle reCAPTCHA failure
                    $errors = $resp->getErrorCodes();
                    $this->logger->error('reCAPTCHA verification failed', ['errors' => $errors, 'remoteIp' => $remoteIp]);
                    $this->addFlash('danger', 'reCAPTCHA verification failed. Please try again.');
                }
            }

            // When reCAPTCHA is disabled, enforce rate limiting and validate form
            if (!$this->useRecaptcha) {
                $limiter = $this->registrationLimiterFactory->create($request->getClientIp());
                $limit = $limiter->consume();

                if (!$limit->isAccepted()) {
                    $this->addFlash('danger', 'You have reached the registration limit. Please try again later.');
                    return $this->redirectToRoute('security_registration');
                }

                // Dispatch BEFORE_USER_REGISTERED event
                $events->dispatch(new RegistrationEvent($user, new RegistrationCode($user)), RegistrationEvent::BEFORE_USER_REGISTERED);

                // Register the user
                $user = $authManager->register($user);
                $regCode = $authManager->createRegistrationCode($user);

                // Dispatch USER_REGISTERED event
                $events->dispatch(new RegistrationEvent($user, $regCode), RegistrationEvent::USER_REGISTERED);

                $this->addFlash('success', 'flash_message.registration_successful');
                return $this->redirectToRoute('security_login');
            }

        }

        // Render the registration form
        return $this->render('auth/registration/register.html.twig', [
            'form' => $form->createView(),
            'recaptcha_site_key' => $this->recaptchaSiteKey,
            'use_recaptcha' => $this->useRecaptcha,
        ]);
    }

    /**
     * Reads email confirmation code from request and validates it.
     */
    #[Route("/registration/confirm/{code}", name: "registration_confirm")]
    public function confirm(
        string $code,
        EntityManagerInterface $manager
    ): RedirectResponse {

        $regCode = $manager->getRepository(RegistrationCode::class)->findOneBy(['code' => $code]);
        if (!$regCode) {
            $this->addFlash('danger', 'Invalid Registration code!');
            return $this->redirectToRoute('security_login');
        }

        $user = $regCode->getUser();
        $user->setIsEmailConfirmed(true);
        $user->setIsActive(true);

        // create candidate
        $candidate = new Candidate();
        $candidate->setUser($user);
        $candidate->setFullName($user->displayName());
        $candidate->setState(new RegisteredState());

        $manager->persist($user);
        $manager->persist($candidate);
        $manager->remove($regCode);
        $manager->flush();

        $this->addFlash('success', 'flash_message.email_confirmed');
        return $this->redirectToRoute('registration_success');
    }

    /**
     * Success page after email confirmation
     */
    #[Route("/registration/success", name: "registration_success")]
    public function success(): \Symfony\Component\HttpFoundation\Response
    {
        // Render the success page
        return $this->render('auth/registration/success.html.twig', []);
    }
}
