<?php

namespace App\Controller\Auth;

use App\Entity\Security\PasswordResetCode;
use App\Entity\User;
use App\Event\ChangePasswordEvent;
use App\Event\PasswordResetEvent;
use App\Event\SecurityEvents;
use App\Form\NewPasswordForm;
use App\Form\ResetPasswordForm;
use App\Security\AuthManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Controller used to manage the application security.
 * See https://symfony.com/doc/current/security/form_login_setup.html.
 */
final class AuthController extends AbstractController
{
    use TargetPathTrait;

    /*
     * The $user argument type (?User) must be nullable because the login page
     * must be accessible to anonymous visitors too.
     */
    #[Route('/login', name: 'security_login', options: ['sitemap' => false])]
    public function login(
        #[CurrentUser] ?User $user,
        AuthenticationUtils $helper,
    ): Response {
        // if user is already logged in, don't display the login page again
        if ($user && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        if ($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        return $this->render('auth/security/login.html.twig', [
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'security_logout', options: ['sitemap' => false])]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * The logout page
     */
    #[Route('/logoutinfo', name: 'info_logout', options: ['sitemap' => false])]
    public function infoLogout(): Response
    {
        return $this->render('auth/security/logout.html.twig', []);
    }

    /**
     * The password reset page
     */
    #[Route('/reset-password', name: 'security_reset', options: ['sitemap' => false], methods: ['GET', 'POST'])]
    public function resetPassword(
        #[CurrentUser] ?User $user,
        Request $request,
        AuthManager $authManager,
        EventDispatcherInterface $events,
        EntityManagerInterface $manager
    ): Response {
        // if user is already logged in, don't display the login page again
        if ($user && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        if ($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        $form = $this->createForm(ResetPasswordForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $identity = $form->get('identity')->getData();
                $userRepository = $manager->getRepository(User::class);
                $user = $userRepository->loadUserByUsername($identity);
                if ($user) {
                    $code = $authManager->createPasswordResetCode($user);
                    $events->dispatch(new PasswordResetEvent($user, $code), SecurityEvents::PASSWORD_RESET_REQUEST);
                    $this->addFlash('success', 'flash_message.password_reset_link_sent');
                } else {
                    $this->addFlash('danger', 'error.user_not_found');
                }
                return $this->redirectToRoute('security_reset');
            }
        }

        // Render the password reset form
        return $this->render('auth/security/password-reset.html.twig', [
            'form' => $form->createView(),
            'error' => null
        ]);
    }

    /**
     * The new password set form page
     */
    #[Route('reset-password/change/{code}', name: 'security-password-change', options: ['sitemap' => false], methods: ['GET', 'POST'])]
    public function newPassword(
        #[CurrentUser] ?User $user,
        string $code,
        Request $request,
        AuthManager $authManager,
        EventDispatcherInterface $events,
        EntityManagerInterface $manager
    ): Response {
        // if user is already logged in, don't display the login page again
        if ($user && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }
        if ($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('admin_dashboard_index');
        }

        // test if hash exists
        /** @var PasswordResetCode $code */
        $code = $manager->getRepository(PasswordResetCode::class)->findOneBy(['code' => $code]);
        if (!$code) {
            // fail with 404 if code not found
            throw $this->createNotFoundException('The code does not exists.');
        }

        $user = $code->getUser();
        $form = $this->createForm(NewPasswordForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($code);
            $manager->flush();

            $authManager->changePassword($user, $user->getPlainPassword());

            $this->addFlash('success', 'flash_message.password_changed');
            $events->dispatch(new ChangePasswordEvent($user), SecurityEvents::PASSWORD_CHANGED);

            return $this->redirectToRoute('security_login');
        }

        // Render the new password form
        return $this->render('auth/security/password-change.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
