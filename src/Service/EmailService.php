<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\AdminOption;
use App\Entity\EmailTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Twig\Environment as Twig;

class EmailService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Twig $twig,
        private readonly string $imagesPath,
    ) {
    }

    /**
     * Sends an emails using a DB-defined template.
     *
     * @param string $templateName The template key/name, e.g. "welcome_email"
     * @param string $locale The locale, e.g. "en"
     * @param array $placeholders Key-value pairs of placeholders and their replacements
     * @param string $toEmail Recipient emails
     * @param string|null $fromEmail (Optional) defaults to something if not provided
     * @throws \RuntimeException if template not found
     * @throws TransportExceptionInterface
     */
    public function sendEmail(
        string $templateName,
        string $locale,
        array $placeholders,
        string $toEmail,
        string $fromName = 'Exedra',
        ?string $fromEmail = null,
    ): void {
        // we fetch the admin options
        $adminOptions = $this->entityManager->getRepository(adminOption::class)->findOneBy(['id' => 1]);

        //  we build a map of all known admin placeholders => real values
        $adminPlaceholders = [
            '%%COMPANY_NAME%%' => $adminOptions->getCompanyName(),
            '%%COMPANY_TELEPHONE%%' => $adminOptions->getCompanyPhone(),
            '%%COMPANY_SALES_EMAIL%%' => $adminOptions->getSalesEmail(),
        ];

        // Combine admin placeholders with the placeholders you explicitly passed in
        //    If there's a conflict in keys, the "custom" placeholders override admin placeholders
        //    or do the reverse if you want admin to override custom.
        $mergedPlaceholders = array_merge($adminPlaceholders, $placeholders);

        // TODO: add new Company from the settings! Company Entitiy removed!

        // we fetch the template from the database
        $emailTemplate = $this->entityManager->getRepository(EmailTemplate::class)->findOneBy(['name' => $templateName, 'locale' => $locale]);

        if (!$emailTemplate) {
            throw new \RuntimeException(sprintf(
                'Email template "%s" for locale "%s" not found in DB.',
                $templateName,
                $locale
            ));
        }

        // Replace placeholders in subject & content
        $subject = $this->replacePlaceholders($emailTemplate->getSubject(), $mergedPlaceholders);
        $content = $this->replacePlaceholders($emailTemplate->getContent(), $mergedPlaceholders);

        // Provide a fallback email address if fromEmail is null or empty
        if (empty($fromEmail)) {
            $fromEmail = 'jobs.bs@exedra.ch';
        }

        // Validate that $fromEmail is a proper email address
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(sprintf('Invalid from email address: "%s"', $fromEmail));
        }

        $email = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to($toEmail)
            ->cc($adminOptions->getSalesEmail())
            ->subject($subject);

        // Create a DataPart object for the image
        $logoPath = $this->imagesPath . '/exedra-logo-light.png';
        $dataPart = new DataPart(new File($logoPath));
        $dataPart = $dataPart->asInline();

        // Attach the part to our Email and generate the actual "cid:..."
        $email->addPart($dataPart);
        $logoCid = 'cid:' . $dataPart->getContentId();

        // Integrate the content into our base layout
        $finalHtml = $this->twig->render('emails/email-base.html.twig', [
            'logo_cid' => $logoCid,
            'emailContent' => $content,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyZip' => $companyZip,
            'companyCity' => $companyCity,
            'companyPhone' => $companyPhone,
            'companyEmail' => $companyEmail,
            'companyLinkedin' => $companyLinkedin,
        ]);

        $email->html($finalHtml);

        // If we want text content as well (optional):
        // $emails->text(strip_tags($content));

        $this->mailer->send($email);
    }

    /**
     * Helper function to replace placeholders in a string.
     *
     * @param string $text
     * @param array  $placeholders  e.g. ['%%USER_FIRSTNAME%%' => 'John', '%%COMPANY_NAME%%' => 'Exedra']
     * @return string
     */
    private function replacePlaceholders(string $text, array $placeholders): string
    {
        // Convert DateTime objects to string using a specified format.
        foreach ($placeholders as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $placeholders[$key] = $value->format('d.m.Y H:i');
            }
        }

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }
}
