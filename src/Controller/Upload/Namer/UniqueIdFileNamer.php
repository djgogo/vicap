<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2025-
 * @license     Proprietary
 */

namespace App\Controller\Upload\Namer;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

final class UniqueIdFileNamer implements NamerInterface
{
    use FileExtensionTrait;

    public function name(object $object, PropertyMapping $mapping): string
    {
        $file = $mapping->getFile($object);

        // Retrieve the original file name and get its base name (without extension)
        $originalName = $file->getClientOriginalName();
        $originalBaseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Optionally, sanitize the original base name by replacing unwanted characters
        // Here we replace any non-alphanumeric, underscore or hyphen character with an underscore.
        $originalBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalBaseName);

        // Generate a unique identifier and remove dots
        $uniqueId = str_replace('.', '', uniqid('', true));

        // Get the file extension
        $extension = $this->getExtension($file);

        if (is_string($extension) && '' !== $extension) {
            return sprintf('%s-%s.%s', $originalBaseName, $uniqueId, $extension);
        }

        return sprintf('%s-%s', $originalBaseName, $uniqueId);
    }
}