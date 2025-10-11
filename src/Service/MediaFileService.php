<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class MediaFileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $uploadsPath,
//        private readonly MediaRepository $mediaRepository
    ) {
    }

    /**
     * Deletes a media file.
     *
     * @param int $id The ID of the file to delete.
     * @param User $currentUser The current authenticated user.
     * @param string $requestContent The JSON payload from the request.
     * @param string $mapping The type of user folder ('/project_files', '/uploads').
     *
     * @return array An array containing 'success' and either 'message' or 'error'.
     */
    public function deleteFile(int $id, User $currentUser, string $requestContent, string $mapping): array
    {
        // security checks
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException('You do not have the necessary permissions.');
        }

        // file directory
        $baseDirectory = $this->uploadsPath . $mapping;

        // Fetch the media file entity
        $mediaFile = null;
        if ($mapping === '/uploads') {
            $mediaFile = $this->entityManager->getRepository(Media::class)->find($id);
        }
        if (!$mediaFile) {
            return ['success' => false, 'error' => 'File not found.'];
        }

        // Decode the JSON payload
        $data = json_decode($requestContent, true);
        $csrfToken = $data['_token'] ?? '';

        // Validate CSRF token
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('delete_file', $csrfToken))) {
            return ['success' => false, 'error' => 'Invalid CSRF token.'];
        }

        // Define the file path
        $filePath = $baseDirectory . '/' . $mediaFile->getFilePath();

        // Attempt to delete the file from the filesystem
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                $this->logger->error('Failed to delete file from filesystem.', ['filePath' => $filePath]);
                return ['success' => false, 'error' => 'Failed to delete file from filesystem.'];
            }
        } else {
            $this->logger->warning('File not found on filesystem.', ['filePath' => $filePath]);
        }

        // Remove the entity from the database
        try {
            $this->entityManager->remove($mediaFile);
            $this->entityManager->flush();

            $this->logger->info('Document file deleted successfully.', ['file_id' => $id]);

            return ['success' => true, 'message' => 'Document file deleted successfully.'];
        } catch (\Exception $e) {
            $this->logger->error('Error deleting Document File.', ['exception' => $e->getMessage()]);
            return ['success' => false, 'error' => 'An error occurred while deleting the file.'];
        }
    }

//    /**
//     * Deletes all Media Files from the filesystem and the database from a given user
//     */
//    public function deleteAllFiles(
//        User $user,
//        string $mapping
//    ): bool {
//        // security checks
//        if (!$this->security->isGranted('ROLE_ADMIN')) {
//            throw new NotFoundHttpException('You do not have the necessary permissions.');
//        }
//
//        // file directory
//        $baseDirectory = $this->uploadsPath . $mapping;
//
//        // fetch all candidate files
//        $mediaFiles = $this->mediaRepository->getAllFilesByUser($user);
//        if (!$mediaFiles) {
//            $this->logger->error('No Candidate Files found.');
//            return false;
//        }
//
//        foreach ($mediaFiles as $mediaFile) {
//            // Define the file path
//            $filePath = $baseDirectory . '/' . $mediaFile->getFilePath();
//
//            // Attempt to delete the file from the filesystem
//            if (file_exists($filePath)) {
//                if (!unlink($filePath)) {
//                    $this->logger->error('Failed to delete file from filesystem.', ['filePath' => $filePath]);
//                    return false;
//                }
//            } else {
//                $this->logger->warning('Candidate File not found on filesystem.', ['filePath' => $filePath]);
//            }
//
//            // Remove the entity from the database
//            try {
//                $this->entityManager->remove($mediaFile);
//                $this->entityManager->flush();
//            } catch (\Exception $e) {
//                $this->logger->critical('Error deleting media file.', ['exception' => $e->getMessage()]);
//                return false;
//            }
//        }
//
//        return true;
//    }

}