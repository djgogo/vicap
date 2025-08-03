<?php
/**
 * @author      Peter Sacco
 * @copyright   websitemaster.ch, 2024-
 * @license     Proprietary
 */

namespace App\Service;

use App\Entity\User;
use Pyrrah\GravatarBundle\GravatarApi;

class GravatarSupplier
{
    const GRAVATAR_SIZE = 256;
    const GRAVATAR_RATING = 'g';
    const GRAVATAR_DEFAULT = 'identicon';

    public function __construct(
        private readonly GravatarApi $gravatar,
        private readonly DevLogger $logger
    ) {
    }

    public function setGravatar(User $user): void
    {
        if ($user->getPhoto()) {
            $this->logger->log($user->getPhoto(), 'has already photo!!!', __LINE__);
            return;
        }
        $image = $this->gravatar->getUrl(
            $user->getEmail(), self::GRAVATAR_SIZE, self::GRAVATAR_RATING,
            self::GRAVATAR_DEFAULT
        );

        $user->setPhoto($image);
    }
}