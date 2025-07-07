<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLike;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserInteraction
{
    private const INTERACTION_REPO_METHODS = [
        'user_like_post_ids' => [UserLike::class, 'findLikedPostIdsByUser'],
        'user_comment_post_ids' => [UserComment::class, 'findCommentPostIdsByUser'],
    ];

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param iterable<int, Post> $posts
     *
     * @return non-empty-array<'user_comment_post_ids'|'user_like_post_ids', array<int>>
     */
    public function getUserInteractionIds(Post|iterable $posts, ?User $user = null): array
    {
        $posts = \is_iterable($posts) ? $posts : [$posts];

        if (null === $user || [] === $posts) {
            return array_fill_keys(array_keys(self::INTERACTION_REPO_METHODS), []);
        }

        $results = [];

        foreach (self::INTERACTION_REPO_METHODS as $key => [$class, $method]) {
            // Dynamic class and method call, avoid keys duplicate, but it's fine
            // @phpstan-ignore-next-line
            $results[$key] = $this->em->getRepository($class)->$method($posts, $user);
        }

        return $results;
    }
}
