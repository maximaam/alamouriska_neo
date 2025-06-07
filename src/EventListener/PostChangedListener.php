<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipCacheManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Post::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Post::class)]
final readonly class PostChangedListener
{
    public function __construct(
        private LiipCacheManager $lcm,
        #[Autowire('%public_dir%')]
        private string $publicDir,
        #[Autowire('%posts_dir%')]
        private string $postsDir,
    ) {
    }

    /**
     * Always clears cache on update
     */
    public function preUpdate(Post $post): void
    {
        $this->removeCachedImages($post);
    }

    public function preRemove(Post $post, PreRemoveEventArgs $event): void
    {
        $this->removeCachedImages($post);
    }

    private function removeCachedImages(Post $post): void
    {
        if (!$image = $post->getPostImageName()) {
            return;
        }

        $this->lcm->remove(\sprintf('%s/%s', $this->postsDir, $image));
        $this->lcm->remove(\sprintf('%s/%s', $this->postsDir, $image.'.webp'));
    }
}
