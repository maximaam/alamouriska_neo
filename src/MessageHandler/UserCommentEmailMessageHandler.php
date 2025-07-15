<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UserCommentEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class UserCommentEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(UserCommentEmailMessage $message): void
    {
        /** @var string $appNotifier */
        $appNotifier = $this->parameterBag->get('app_notifier_email');

        /** @var string $appName */
        $appName = $this->parameterBag->get('app_name');

        $email = (new TemplatedEmail())
            ->from(new Address($appNotifier, $appName))
            ->to(new Address($appNotifier, $appName))
            // ->to($message->receiverEmail)
            ->bcc(...$message->receiverEmails)
            ->subject($this->translator->trans('email.post_comment.subject'))
            ->htmlTemplate('emails/user_comment.fr.html.twig')
            ->context([
                'commentator' => $message->senderPseudo,
                'entityUrl' => $message->entityUrl,
            ]);

        $this->mailer->send($email);
    }
}
