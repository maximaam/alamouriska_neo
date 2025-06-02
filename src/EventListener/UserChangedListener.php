<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipCacheManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
final class UserChangedListener
{
    public function __construct(
        private readonly LiipCacheManager $lcm,

        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
        #[Autowire('%avatars_dir%')]
        private readonly string $avatarsDir,
    ) {
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        $this->pseudoChanged($user, $event);
        $this->removeCachedAvatars($user);
    }

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
        $avatarFile = sprintf('%s/%s/%s', $this->publicDir, $this->avatarsDir, $user->getAvatarName());

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

    private function removeCachedAvatars(User $user): void
    {
        if (null === $avatarName = $user->getAvatarName()) {
            return;
        }

        $this->lcm->remove(sprintf('%s/%s', $this->avatarsDir, $avatarName));
        $this->lcm->remove(sprintf('%s/%s', $this->avatarsDir, $avatarName.'.webp'));
    }
}
