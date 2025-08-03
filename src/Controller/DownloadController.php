<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Controller;

use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use ZipArchive;

#[Route('/download')]
class DownloadController extends AbstractController
{
    #[Route('/application/{id}', name: 'public_applications_documents_download', requirements: ['id' => '\d+'])]
    public function publicDownloadApplicationDocuments(
        Request $request,
        EntityManagerInterface $entityManager,
        int $id
    ): StreamedResponse {
        $token = $request->query->get('token');
        $expected = hash_hmac('sha256', $id, $this->getParameter('kernel.secret'));

        if (!$token || !hash_equals($expected, $token)) {
            throw $this->createAccessDeniedException('Invalid or missing token.');
        }

        $application = $entityManager->getRepository(Application::class)->find($id);
        if (!$application) {
            throw $this->createNotFoundException('Application not found!');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'application_documents_');
        $zip = new ZipArchive();

        if ($zip->open($tmpFile, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not open temporary file for writing.");
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/media/application_files';

        foreach ($application->getApplicationFiles() as $fileEntity) {
            $fileName = $fileEntity->getFileName();
            $filePath = $uploadDir . '/' . $fileName;
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $fileName);
            }
        }

        $zip->close();

        $response = new StreamedResponse(function () use ($tmpFile) {
            readfile($tmpFile);
            unlink($tmpFile);
        });

        $response->headers->set('Content-Type', 'application/zip');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'bewerbung_dokumente.zip'
        );
        $response->headers->set('Content-Disposition', $disposition);
        if (file_exists($tmpFile)) {
            $response->headers->set('Content-Length', filesize($tmpFile));
        }

        return $response;
    }
}