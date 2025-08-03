<?php

namespace App\Twig;

use App\Entity\User;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * User related twig functions.
 *
 * @package App\Twig
 */
class LiipImageExtension extends AbstractExtension
{
    private Packages $assetPackages;

    /**
     * Inject the Asset Packages service.
     */
    public function __construct(Packages $assetPackages)
    {
        $this->assetPackages = $assetPackages;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('liip_image', [$this, 'displayImage']),
        ];
    }

    /**
     * Returns an URL path to user's picture. // todo: not active right now - this is how liip is working with twig - use this if not VichUploader!
     */
    public function displayImage(User $user, string $filter = 'user_avatar'): string
    {
        $filename = $user->getPhoto();

        if (!$filename) {
            return '';
        }

        // Check if the filename is an absolute URL.
        if (preg_match('/^(https?:\/\/)|(\/\/)/', $filename)) {
            return $filename;
        }

        // Construct the relative path to the user's photo.
        // Assuming photos are stored in 'public/media/users'.
        $relativePath = 'media/users/' . $filename;

        // Generate the URL with the LiipImagine filter applied.
        // The URL structure will be '/media/cache/{filter}/media/users/{filename}'
        return $this->assetPackages->getUrl($relativePath, null) . "?liip_imagine_filter={$filter}";
    }
}
