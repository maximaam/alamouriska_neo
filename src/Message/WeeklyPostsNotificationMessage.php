<?php

declare(strict_types=1);

namespace App\Message;

final readonly class WeeklyPostsNotificationMessage
{
    /**
     * @param array<string, int> $posts
     */
    public function __construct(
        public array $posts,
        public string $userEmail,
    ) {
    }
}
