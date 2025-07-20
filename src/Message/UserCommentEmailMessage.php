<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UserCommentEmailMessage
{
    /**
     * @param string[] $receiverEmails
     */
    public function __construct(
        public string $senderPseudo,
        public array $receiverEmails, // Post owner and commentators
        public string $entityUrl,
    ) {
    }
}
