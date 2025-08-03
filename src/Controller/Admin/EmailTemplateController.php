<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\DTO\Email\EmailTemplateGroup;
use App\DTO\Email\EmailTemplatesSettings;
use App\DTO\Email\EmailTemplateTranslation;
use App\Entity\EmailTemplate;
use App\Entity\User;
use App\Form\EmailTemplates\EmailTemplatesSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles email templates settings.
 *
 * @package App\Controller
 */
#[Route('/admin/settings')]
#[IsGranted(User::ROLE_ADMIN)]
class EmailTemplateController extends AbstractController
{
    /**
     * All Email Templates for the Authentication process
     */
    #[Route('/email-template/auth', name: 'admin_settings_email_templates_auth', methods: ['GET', 'POST'])]
    public function editAuthTemplates(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Define the list of template names and locales
        $templateNames = [
            'user_password_reset',
            'user_password_changed'
        ];

        $locales = ['de'];

        // Initialize EmailTemplatesSettings DTO
        $settings = new EmailTemplatesSettings();

        foreach ($templateNames as $templateName) {
            $group = new EmailTemplateGroup($templateName);

            foreach ($locales as $locale) {
                // Find the EmailTemplate for this name and locale
                $emailTemplate = $em->getRepository(EmailTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                if (!$emailTemplate) {
                    // If not found, create a new one
                    $emailTemplate = new EmailTemplate();
                    $emailTemplate->setName($templateName);
                    $emailTemplate->setLocale($locale);
                    $emailTemplate->setSubject('');
                    $emailTemplate->setContent('');
                    // Do not persist yet
                }

                // Create the EmailTemplateTranslation DTO
                $translation = new EmailTemplateTranslation();
                $translation->locale = $emailTemplate->getLocale();
                $translation->subject = $emailTemplate->getSubject();
                $translation->content = $emailTemplate->getContent();

                $group->translations->add($translation);
            }

            $settings->groups->add($group);
        }

        // Create the form
        $form = $this->createForm(EmailTemplatesSettingsType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EmailTemplatesSettings $settings */
            $settings = $form->getData();

            foreach ($settings->groups as $group) {
                $templateName = $group->name;

                foreach ($group->translations as $translation) {
                    $locale = $translation->locale;
                    $subject = $translation->subject;
                    $content = $translation->content;

                    // Find or create the EmailTemplate
                    $emailTemplate = $em->getRepository(EmailTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                    if (!$emailTemplate) {
                        $emailTemplate = new EmailTemplate();
                        $emailTemplate->setName($templateName);
                        $emailTemplate->setLocale($locale);
                        $em->persist($emailTemplate);
                    }

                    // Update fields
                    $emailTemplate->setSubject($subject);
                    $emailTemplate->setContent($content);

                    $em->persist($emailTemplate);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Email templates updated successfully.');

            return $this->redirectToRoute('admin_settings_email_templates_auth');
        }

        return $this->render('admin/settings/emailTemplates/auth-email.html.twig', [
            'currentUser' => $currentUser,
            'form' => $form->createView(),
        ]);
    }
}
