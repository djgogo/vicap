<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DocumentUploader
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
        private readonly string $uploadsPath,
    ) {
    }

    /**
     * Uploads a file to the specified mapping directory.
     *
     * @param UploadedFile $file
     * @param string $mapping
     * @return string The new filename
     */
    public function upload(UploadedFile $file, string $mapping): string
    {
        $targetDirectory = $this->uploadsPath . $mapping;

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            $this->logger->error('File upload failed during upload of file .', [
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Failed to upload file.');
        }

        return $fileName;
    }

    /**
     * Validates a general uploaded file (e.g., PDF, DOC, TXT).
     *
     * @param UploadedFile $file
     * @return array An array of error messages, empty if valid
     */
    public function validate(UploadedFile $file): array
    {
        $errors = [];

        // Define constraints for general files
        $constraints = new Assert\Collection([
            'file' => [
                new Assert\File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid file (PDF, DOC, DOCX, TXT).',
                ])
            ]
        ]);

        // Validate the file
        $validationResult = $this->validator->validate(['file' => $file], $constraints);

        if (count($validationResult) > 0) {
            // Collect error messages
            foreach ($validationResult as $violation) {
                $errors[] = $violation->getMessage();
            }
            $this->logger->error('File upload failed during upload of file.', ['errors' => $errors]);
        }

        return $errors;
    }

}