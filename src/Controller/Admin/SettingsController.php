<?php

namespace App\Controller\Admin;

use App\Entity\AdminOption;
use App\Entity\User;
use App\Form\SettingsType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Handles admin user settings.
 *
 * @package App\Controller
 */
#[Route('/admin/settings')]
#[IsGranted(User::ROLE_ADMIN)]
class SettingsController extends AbstractController
{
    /**
     * AJAX edit option switchers.
     */
    #[Route('/options/', name: 'admin_settings_options_edit', methods: ['GET', 'POST'])]
    public function index(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {

        $options = $entityManager->getRepository(AdminOption::class)->findOneBy(['id' => 01]);
        if (!$options) {
            $options = new AdminOption();
        }

        $form = $this->createForm(SettingsType::class, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($options);
            $entityManager->flush();
            $this->addFlash('success', 'flash_message.options_updated');

            return $this->redirectToRoute('admin_settings_options_edit');
        }

        return $this->render('admin/settings/settings.html.twig', [
            'options' => $options,
            'currentUser' => $currentUser,
            'form' => $form
        ]);
    }

    /**
     * send test email
     */
    #[Route('/email-test/{id}', name: 'admin_settings_email_test', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function sendTestEmail(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        EmailService $emailService,
        int $id = null
    ): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $request = $requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'de';

        // send test email to user
        $emailService->sendEmail(
            templateName:'user_password_changed',
            locale: $locale,
            placeholders: [
                '%%USER_FIRSTNAME%%' => $user->getFirstName(),
            ],
            toEmail: $user->getEmail(),
            fromName: $user->getFirstName() . ' ' . $user->getLastName(),
        );

        return $this->redirectToRoute('admin_settings_options_edit');
    }
}
