<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UserCommentEmailMessage
{
    public function __construct(
        public string $senderPseudo,
        public string $receiverPseudo,
        public string $receiverEmail,
        public string $entityUrl,
    ) {
    }
}
