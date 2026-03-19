<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final class IndexCacheInvalidator
{
    private bool $shouldInvalidate = false;
    private const string CACHE_TAG = 'index_newest_posts';

    public function __construct(
        #[Autowire(service: 'app.'.self::CACHE_TAG)]
        private readonly TagAwareCacheInterface $cache
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $uow = $args->getObjectManager()->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates(),
            $uow->getScheduledEntityDeletions()
        );

        foreach ($entities as $entity) {
            if ($entity instanceof Post) {
                $this->shouldInvalidate = true;
                break;
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postFlush(): void
    {
        if ($this->shouldInvalidate) {
            $this->cache->invalidateTags([self::CACHE_TAG]);
            $this->shouldInvalidate = false;
        }
    }
}
