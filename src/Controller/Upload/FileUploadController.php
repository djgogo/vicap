<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\Controller\Upload;

use App\Entity\Media;
use App\Service\DevLogger;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles file upload.
 *
 * @package App\Controller
 */
#[Route('/upload')]
class FileUploadController extends AbstractController
{
    /**
     * AJAX media file upload
     */
    #[Route('/media-file/', name: 'media_files_upload', methods: ['POST'])]
    public function uploadMediaFile(
        Security $security,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $uploader,
        DevLogger $logger,
    ): JsonResponse {

        // security checks
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('You do not have the necessary permissions.');
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('mediaFile');

        if ($file) {

            // Capture file properties before moving
            $fileOriginalName = $file->getClientOriginalName();
            $fileNameWithoutExt = pathinfo($fileOriginalName, PATHINFO_FILENAME);
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            // Validate the file
            $errors = $uploader->validate($file);

            if (!empty($errors)) {
                $logger->log($errors, 'File validation error.');
                return $this->json(['errors' => $errors], 400);
            }

            try {
                // Process the file upload
                $fileName = $uploader->upload($file, 'media_files');

                $mediaFile = new Media();
                $mediaFile->setFileName($fileNameWithoutExt);
                $mediaFile->setFilePath($fileName);
                $mediaFile->setFileType($fileExtension);
                $mediaFile->setFileSize($fileSize);

                $entityManager->persist($mediaFile);
                $entityManager->flush();

                // In your upload controller
                return $this->json([
                    'success' => true,
                    'message' => 'File uploaded successfully.',
                    'file' => [
                        'id' => $mediaFile->getId(),
                        'fileName' => $mediaFile->getFileName(),
                        'filePath' => $mediaFile->getFilePath(),
                        'fileType' => $mediaFile->getFileType(),
                        'fileSize' => $mediaFile->getFileSize(),
                        'created' => $mediaFile->getCreated()->format('Y-m-d H:i'),
                        'deleteUrl' => $this->generateUrl('media_files_delete', ['id' => $mediaFile->getId()]),
                        // Dynamically determine classes based on file-type
                        'iconClass' => $this->getIconClass($mediaFile->getFileType()),
                        'colorClass' => $this->getColorClass($mediaFile->getFileType()),
                        'bgClass' => $this->getBgClass($mediaFile->getFileType()),
                    ]
                ], 200);
            } catch (\Exception $e) {
                return $this->json(['error' => 'File upload failed.'], 500);
            }
        }

        $logger->log('No file uploaded in the request.');
        return $this->json(['error' => 'No file uploaded.'], 400);
    }

    private function getIconClass(string $fileType): string
    {
        $mapping = [
            'pdf' => 'ri-file-pdf-fill',
            'doc' => 'ri-file-word-fill',
            'docx' => 'ri-file-word-fill',
            'txt' => 'ri-file-text-fill',
        ];

        return $mapping[strtolower($fileType)] ?? 'ri-file-zip-fill';
    }

    private function getColorClass(string $fileType): string
    {
        $mapping = [
            'pdf' => 'text-secondary',
            'doc' => 'text-primary',
            'docx' => 'text-primary',
            'txt' => 'text-danger',
        ];

        return $mapping[strtolower($fileType)] ?? 'text-primary';
    }

    private function getBgClass(string $fileType): string
    {
        $mapping = [
            'pdf' => 'bg-secondary-subtle',
            'doc' => 'bg-primary-subtle',
            'docx' => 'bg-primary-subtle',
            'txt' => 'bg-danger-subtle',
        ];

        return $mapping[strtolower($fileType)] ?? 'bg-primary-subtle';
    }

}