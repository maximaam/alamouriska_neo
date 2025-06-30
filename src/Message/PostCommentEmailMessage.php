<?php

declare(strict_types=1);

namespace App\Message;

final class PostCommentEmailMessage
{
    public function __construct(
        public string $senderPseudo,
        public string $receiverPseudo,
        public string $receiverEmail,
        public int $postId,
        public string $postTitle,
        public string $postType,
        public string $postSlug
    ) {}
}
