<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Cache\Contract\PostCacheInvalidationAwareInterface;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final class PostCacheInvalidator
{
    private bool $shouldInvalidate = false;

    public function __construct(
        #[Autowire(service: 'app.'.Post::CACHE_TAG)]
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $uow = $args->getObjectManager()->getUnitOfWork();
        foreach ($this->getScheduledEntities($uow) as $entity) {
            if ($entity instanceof PostCacheInvalidationAwareInterface) {
                $this->shouldInvalidate = true;

                return;
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postFlush(): void
    {
        if ($this->shouldInvalidate) {
            $this->cache->invalidateTags([Post::CACHE_TAG]);
            $this->shouldInvalidate = false;
        }
    }

    /**
     * @return iterable<int, object>
     */
    private function getScheduledEntities(UnitOfWork $uow): iterable
    {
        yield from $uow->getScheduledEntityInsertions();
        yield from $uow->getScheduledEntityUpdates();
        yield from $uow->getScheduledEntityDeletions();
    }
}
