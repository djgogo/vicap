<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\DTO\Term\TermTemplateGroup;
use App\DTO\Term\TermTemplatesSettings;
use App\DTO\Term\TermTemplateTranslation;
use App\Entity\TermTemplate;
use App\Entity\User;
use App\Form\TermTemplates\TermTemplatesSettingsType;
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
class TermTemplateController extends AbstractController
{
    /**
     * All Term Templates for the Impressum Page
     */
    #[Route('/term-template/impressum', name: 'admin_settings_term_templates_impressum', methods: ['GET', 'POST'])]
    public function editImpressumTemplate(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Define the list of template names and locales
        $templateNames = [
            'website_impressum',
        ];

        $locales = ['de'];

        // Initialize TermTemplatesSettings DTO
        $settings = new TermTemplatesSettings();

        foreach ($templateNames as $templateName) {
            $group = new TermTemplateGroup($templateName);

            foreach ($locales as $locale) {
                // Find the TermTemplate for this name and locale
                $termTemplate = $em->getRepository(TermTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                if (!$termTemplate) {
                    // If not found, create a new one
                    $termTemplate = new TermTemplate();
                    $termTemplate->setName($templateName);
                    $termTemplate->setLocale($locale);
                    $termTemplate->setContent('');
                    // Do not persist yet
                }

                // Create the TermTemplateTranslation DTO
                $translation = new TermTemplateTranslation();
                $translation->locale = $termTemplate->getLocale();
                $translation->content = $termTemplate->getContent();

                $group->translations->add($translation);
            }

            $settings->groups->add($group);
        }

        // Create the form
        $form = $this->createForm(TermTemplatesSettingsType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TermTemplatesSettings $settings */
            $settings = $form->getData();

            foreach ($settings->groups as $group) {
                $templateName = $group->name;

                foreach ($group->translations as $translation) {
                    $locale = $translation->locale;
                    $content = $translation->content;

                    // Find or create the TermTemplate
                    $termTemplate = $em->getRepository(TermTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                    if (!$termTemplate) {
                        $termTemplate = new TermTemplate();
                        $termTemplate->setName($templateName);
                        $termTemplate->setLocale($locale);
                        $em->persist($termTemplate);
                    }

                    // Update fields
                    $termTemplate->setContent($content);

                    $em->persist($termTemplate);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Term templates updated successfully.');

            return $this->redirectToRoute('admin_settings_term_templates_impressum');
        }

        return $this->render('admin/settings/termTemplates/impressum.html.twig', [
            'currentUser' => $currentUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * All Term Templates for the Privacy Policies
     */
    #[Route('/term-template/privacy-policy', name: 'admin_settings_term_templates_privacy_policy', methods: ['GET', 'POST'])]
    public function editPrivacyPolicyTemplate(
        #[CurrentUser] User $currentUser,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Define the list of template names and locales
        $templateNames = [
            'website_privacy_policy',
        ];

        $locales = ['de'];

        // Initialize TermTemplatesSettings DTO
        $settings = new TermTemplatesSettings();

        foreach ($templateNames as $templateName) {
            $group = new TermTemplateGroup($templateName);

            foreach ($locales as $locale) {
                // Find the TermTemplate for this name and locale
                $termTemplate = $em->getRepository(TermTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                if (!$termTemplate) {
                    // If not found, create a new one
                    $termTemplate = new TermTemplate();
                    $termTemplate->setName($templateName);
                    $termTemplate->setLocale($locale);
                    $termTemplate->setContent('');
                    // Do not persist yet
                }

                // Create the TermTemplateTranslation DTO
                $translation = new TermTemplateTranslation();
                $translation->locale = $termTemplate->getLocale();
                $translation->content = $termTemplate->getContent();

                $group->translations->add($translation);
            }

            $settings->groups->add($group);
        }

        // Create the form
        $form = $this->createForm(TermTemplatesSettingsType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TermTemplatesSettings $settings */
            $settings = $form->getData();

            foreach ($settings->groups as $group) {
                $templateName = $group->name;

                foreach ($group->translations as $translation) {
                    $locale = $translation->locale;
                    $content = $translation->content;

                    // Find or create the TermTemplate
                    $termTemplate = $em->getRepository(TermTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

                    if (!$termTemplate) {
                        $termTemplate = new TermTemplate();
                        $termTemplate->setName($templateName);
                        $termTemplate->setLocale($locale);
                        $em->persist($termTemplate);
                    }

                    // Update fields
                    $termTemplate->setContent($content);

                    $em->persist($termTemplate);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Term templates updated successfully.');

            return $this->redirectToRoute('admin_settings_term_templates_privacy_policy');
        }

        return $this->render('admin/settings/termTemplates/privacy-policy.html.twig', [
            'currentUser' => $currentUser,
            'form' => $form->createView(),
        ]);
    }

}
