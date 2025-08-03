<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Service;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AvatarFileService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Security $security,
        private readonly string $uploadsPath,
    ) {
    }

    /**
     * Deletes a user avatar file.
     */
    public function deleteAvatar(?string $fileName, string $mapping): void
    {
        // security checks
        if (!$this->security->isGranted('ROLE_ADMIN') && !$this->security->isGranted('ROLE_CANDIDATE') && !$this->security->isGranted('ROLE_EMPLOYEE')) {
            throw new NotFoundHttpException('You do not have the necessary permissions.');
        }

        if ($fileName === null) {
            return;
        }

        // file directory
        $baseDirectory = $this->uploadsPath . $mapping;

        // Define the file path
        $filePath = $baseDirectory . '/' . $fileName;

        // Attempt to delete the file from the filesystem
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                $this->logger->error('Failed to delete user avatar from filesystem.', ['filePath' => $filePath]);
            }
        } else {
            $this->logger->warning('User avatar file not found on filesystem.', ['filePath' => $filePath]);
        }

    }
}