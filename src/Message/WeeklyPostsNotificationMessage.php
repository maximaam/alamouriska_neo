<?php

declare(strict_types=1);

namespace App\Message;

final readonly class WeeklyPostsNotificationMessage
{
    public function __construct(
        public array $weeklyPosts,
        public string $userEmail,
    ) {
    }
}
