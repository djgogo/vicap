<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2023-
 * @license     Proprietary
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileUploader
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ValidatorInterface $validator,
        private readonly DevLogger $debug,
        private readonly array $vichConfig
    ) {
    }

    /**
     * Uploads a file to the specified mapping directory.
     *
     * @param UploadedFile $file
     * @param string $mapping
     * @return string The new filename
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $mapping): string
    {
        if (!isset($this->vichConfig[$mapping])) {
            throw new \InvalidArgumentException(sprintf('Mapping "%s" is not defined in VichUploader configuration.', $mapping));
        }

        $targetDirectory = $this->vichConfig[$mapping]['upload_destination'];

        $this->debug->log('File Uploader: ' . $targetDirectory);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            $this->debug->log($e);
            throw new \Exception('Failed to upload file.');
        }

        return $fileName;
    }

    /**
     * Validates an uploaded media file.
     *
     * @param UploadedFile $file
     * @return array An array of error messages, empty if valid
     */
    public function validate(UploadedFile $file): array
    {
        $errors = [];

        // Define constraints
        $constraints = new Assert\Collection([
            'file' => [
                new Assert\File([
                    'maxSize' => '10000k',
                    'mimeTypes' => [
                        // plain text
                        'text/plain', // .txt
                        // images
                        'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
                        // documents
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',      // .xlsx
                    ],
                    'mimeTypesMessage' => 'Please upload a valid media file',
                ])
            ]
        ]);

        // Validate file
        $validationResult = $this->validator->validate(['file' => $file], $constraints);

        if (0 !== count($validationResult)) {
            // Collect errors
            foreach ($validationResult as $violation) {
                $errors[] = $violation->getMessage();
            }
        }

        return $errors;
    }
}