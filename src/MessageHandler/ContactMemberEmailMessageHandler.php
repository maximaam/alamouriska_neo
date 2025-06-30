<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ContactMemberEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class ContactMemberEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(ContactMemberEmailMessage $message): void
    {
        /** @var string $appNotifier */
        $appNotifier = $this->parameterBag->get('app_notifier_email');

        /** @var string $appName */
        $appName = $this->parameterBag->get('app_name');

        $email = (new TemplatedEmail())
            ->from(new Address($appNotifier, $appName))
            ->to($message->receiverEmail)
            ->subject($this->translator->trans('email.contact_member.subject'))
            ->htmlTemplate('emails/contact_member.fr.html.twig')
            ->context([
                'sender' => $message->senderPseudo,
                'receiver' => $message->receiverPseudo,
                'message' => $message->senderMessage,
            ]);

        $this->mailer->send($email);
    }
}
