<?php

namespace App\Twig;

use App\Entity\TradeCategory;
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
class TradeCategoryExtension extends AbstractExtension
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
            new TwigFunction('category_image', [$this, 'categoryImage']),
        ];
    }

    /**
     * Returns an URL path to category's picture.
     */
    public function categoryImage(TradeCategory $tradeCategory, string $filter = 'user_avatar')
    {
        // Path to the default image relative to the public directory
        $defaultImagePath = 'assets/images/company-default.png';
        $defaultImageUrl = $this->packages->getUrl($defaultImagePath);

        $path = $tradeCategory->getImage();
        if (!$path) {
            return $defaultImageUrl;
        }

        if (preg_match('/^(https?:\/\/)|(\/\/)/', $path)) {
            return $path;
        }

        return $this->uploaderHelper->asset($tradeCategory, 'imageFile');
//        return $this->cacheManager->generateUrl($path, $filter);
    }
}
