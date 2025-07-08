<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLike;
use App\Entity\Wall;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserInteraction
{
    private const INTERACTION_REPO_METHODS = [
        'user_like_interaction_ids' => [UserLike::class, 'getUserInteractionIds'],
        'user_comment_interaction_ids' => [UserComment::class, 'getUserInteractionIds'],
    ];

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param Post|Wall|iterable<Post|Wall> $entities
     *
     * @return array<'user_like_interaction_ids'|'user_comment_interaction_ids', int[]>
     */
    public function getUserInteractionIds(Post|Wall|iterable $entities, string $entityName, ?User $user = null): array
    {
        $entities = \is_iterable($entities) ? $entities : [$entities];

        if (null === $user || [] === $entities) {
            return array_fill_keys(array_keys(self::INTERACTION_REPO_METHODS), []);
        }

        $results = [];

        foreach (self::INTERACTION_REPO_METHODS as $key => [$class, $method]) {
            // Dynamic class and method call, avoid keys duplicate, but it's fine
            // @phpstan-ignore-next-line
            $results[$key] = $this->em->getRepository($class)->$method($entities, $entityName, $user);
        }

        return $results;
    }
}
