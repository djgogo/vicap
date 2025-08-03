<?php

namespace App\Twig;

use App\Entity\User;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * User related twig functions.
 *
 * @package App\Twig
 */
class UserExtension extends AbstractExtension
{
//    private CacheManager $cacheManager;
    private UploaderHelper $uploaderHelper;
    private RequestStack $requestStack;

    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper, RequestStack $requestStack)
    {
//        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_photo', [$this, 'userPhoto']),
        ];
    }

    /**
     * Returns the URL path to user's picture.
     */
    public function userPhoto(User $user, string $filter = 'user_avatar'): string
    {
        $photo = $user->getPhoto();
        if (!$photo) {
            $request = $this->requestStack->getCurrentRequest();
            return $request->getSchemeAndHttpHost() . '/assets/images/users/user-dummy-img.jpg';
        }

        // Use filter_var to check if $photo is a valid URL
        if (filter_var($photo, FILTER_VALIDATE_URL)) {
            return $photo;
        }

        return $this->uploaderHelper->asset($user, 'photoFile');
//        return $this->cacheManager->generateUrl($path, $filter); // todo: cacheManager is working, but with this the redirect from 302 to 200 is not working!
    }
}
