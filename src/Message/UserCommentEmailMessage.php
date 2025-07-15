<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UserCommentEmailMessage
{
    public function __construct(
        public string $senderPseudo,
        public array $receiverEmails, // Post owner and commentators
        public string $entityUrl,
    ) {
    }
}
