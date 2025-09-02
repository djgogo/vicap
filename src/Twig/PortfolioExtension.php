<?php

namespace App\Twig;

use App\Entity\Portfolio;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Portfolio related twig functions.
 *
 * @package App\Twig
 */
class PortfolioExtension extends AbstractExtension
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
            new TwigFunction('portfolio_image', [$this, 'portfolioImage']),
        ];
    }

    /**
     * Returns an URL path to portfolio's picture.
     */
    public function portfolioImage(Portfolio $portfolio, string $filter = 'portfolio_440')
    {
        // Path to the default image relative to the public directory
        $defaultImagePath = 'assets/images/company-default.png';
        $defaultImageUrl = $this->packages->getUrl($defaultImagePath);

        $path = $portfolio->getImage();
        if (!$path) {
            return $defaultImageUrl;
        }

        if (preg_match('/^(https?:\/\/)|(\/\/)/', $path)) {
            return $path;
        }

        return $this->uploaderHelper->asset($portfolio, 'imageFile');
//        return $this->cacheManager->generateUrl($path, $filter);
    }
}
