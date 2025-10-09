<?php

namespace App\Twig;

use App\Entity\Blog;
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
class BlogExtension extends AbstractExtension
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
            new TwigFunction('blog_image', [$this, 'blogImage']),
        ];
    }

    /**
     * Returns an URL path to portfolio's picture.
     */
    public function blogImage(Blog $blog, string $filter = 'blog_780')
    {
        // Path to the default image relative to the public directory
        $defaultImagePath = 'assets/images/blog/blog-default.jpg';
        $defaultImageUrl = $this->packages->getUrl($defaultImagePath);

        $path = $blog->getImage();
        if (!$path) {
            return $defaultImageUrl;
        }

        if (preg_match('/^(https?:\/\/)|(\/\/)/', $path)) {
            return $path;
        }

        return $this->uploaderHelper->asset($blog, 'imageFile');
//        return $this->cacheManager->generateUrl($path, $filter);
    }
}
