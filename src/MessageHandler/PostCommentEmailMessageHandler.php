<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PostCommentEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment as Twig;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class PostCommentEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Twig $twig,
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    public function __invoke(PostCommentEmailMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->parameterBag->get('app_notifier_email'), $this->parameterBag->get('app_name')))
            ->to($message->receiverEmail)
            ->subject($this->translator->trans('email.post_comment.subject'))
            ->htmlTemplate('emails/post_comment.fr.html.twig')
            ->context([
                'sender' => $message->senderPseudo,
                'receiver' => $message->receiverPseudo,
                'post_title' => $message->postTitle,
                'post_type' => $message->postType,
                'post_id' => $message->postId,
                'post_slug' => $message->postSlug,
            ]);

        $this->mailer->send($email);
    }
}
