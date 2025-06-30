<?php

declare(strict_types=1);

namespace App\Message;

final class ContactMemberEmailMessage
{
    public function __construct(
        public string $senderPseudo,
        public string $senderMessage,
        public string $receiverPseudo,
        public string $receiverEmail,
    ) {
    }
}
