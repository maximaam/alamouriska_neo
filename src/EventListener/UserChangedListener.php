<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
// use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipCacheManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
final readonly class UserChangedListener
{
    public function __construct(
        private LiipCacheManager $lcm,
        #[Autowire('%avatars_dir%')]
        private string $avatarsDir,
    ) {
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        // $this->pseudoChanged($user, $event);
        $this->removeCachedAvatars($user, $event);
    }

    /*
    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
    }
    */

    /**
     * Rename uploaded image when pseudo changes.
     * This is with Vich config being:
     * service: Vich\UploaderBundle\Naming\PropertyNamer
     * currently deactivated.
     */
    /*
    private function pseudoChanged(User $user, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('pseudo')) {
            return;
        }

        if (null === $user->getAvatarName()) {
            return;
        }

        $previousPseudo = $event->getOldValue('pseudo');
        $newPseudo = $event->getNewValue('pseudo');
        $avatarFile = \sprintf('%s/%s/%s', $this->publicDir, $this->avatarsDir, $user->getAvatarName());

        if (!file_exists($avatarFile)) {
            return;
        }

        $avatarFileNew = str_replace($previousPseudo, $newPseudo, $avatarFile);
        $avatarNameNew = str_replace($previousPseudo, $newPseudo, $user->getAvatarName());

        // Remove Avatars with old name
        $this->removeCachedAvatars($user);
        rename($avatarFile, $avatarFileNew);
        $user->setAvatarName($avatarNameNew);
    }
    */

    private function removeCachedAvatars(User $user, PreUpdateEventArgs $event): void
    {
        $avatarName = $user->getAvatarName();

        if (null === $avatarName && $event->hasChangedField('avatarName')) {
            $avatarName = $event->getOldValue('avatarName');
        }

        if (null === $avatarName) {
            return;
        }

        $this->lcm->remove(\sprintf('%s/%s', $this->avatarsDir, $avatarName));
        $this->lcm->remove(\sprintf('%s/%s', $this->avatarsDir, $avatarName.'.webp'));
    }
}
