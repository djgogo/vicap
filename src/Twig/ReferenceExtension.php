<?php

namespace App\Twig;

use App\Entity\Reference;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * User related twig functions.
 *
 * @package App\Twig
 */
class ReferenceExtension extends AbstractExtension
{
//    private CacheManager $cacheManager;
    private UploaderHelper $uploaderHelper;
    private Packages $packages;

    public function __construct(Packages $assetPackages, CacheManager $cacheManager, UploaderHelper $uploaderHelper)
    {
        $this->packages = $assetPackages;
//        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('reference_logo', [$this, 'referenceLogo']),
        ];
    }

    /**
     * Returns an URL path to reference logo.
     */
    public function referenceLogo(Reference $reference, string $filter = 'reference_thumb')
    {
        // Path to the default image relative to the public directory
        $defaultImagePath = 'assets/images/companies/img-2.png';
        $defaultImageUrl = $this->packages->getUrl($defaultImagePath);

        $path = $reference->getLogo();
        if (!$path) {
            return $defaultImageUrl;
        }

        return $this->uploaderHelper->asset($reference, 'imageFile');
//        return $this->cacheManager->generateUrl($path, $filter);
    }
}
