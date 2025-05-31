<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipCacheManager;

// #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
final class UserChangedListener
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly LiipCacheManager $lcm,
    ) {
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        $this->pseudoChanged($user, $event);
        $this->removeCachedAvatars([$user->getAvatarName()]);
    }

    private function pseudoChanged(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('pseudo')) {
            $previousPseudo = $event->getOldValue('pseudo');
            $newPseudo = $event->getNewValue('pseudo');
            $avatarFile = sprintf(
                '%s/%s/%s',
                $this->parameterBag->get('public_dir'),
                $this->parameterBag->get('avatars_dir'),
                $user->getAvatarName()
            );

            if (null !== $user->getAvatarName() && file_exists($avatarFile)) {
                $avatarFileNew = str_replace($previousPseudo, $newPseudo, $avatarFile);
                $avatarNameNew = str_replace($previousPseudo, $newPseudo, $user->getAvatarName());
                
                // Remove Avatars with old name
                $this->removeCachedAvatars([$user->getAvatarName()]);
                
                rename($avatarFile, $avatarFileNew);
                
                $user->setAvatarName($avatarNameNew);
            }
        }
    }

    private function removeCachedAvatars(array $avatars): void
    {
        foreach ($avatars as $avatar) {
            $this->lcm->remove(sprintf('%s/%s', $this->parameterBag->get('avatars_dir'), $avatar));
        }
    }
}
