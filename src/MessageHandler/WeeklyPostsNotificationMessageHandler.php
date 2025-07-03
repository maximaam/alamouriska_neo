<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\WeeklyPostsNotificationMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class WeeklyPostsNotificationMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function __invoke(WeeklyPostsNotificationMessage $message): void
    {
        /** @var string $appNotifier */
        $appNotifier = $this->parameterBag->get('app_notifier_email');

        /** @var string $appName */
        $appName = $this->parameterBag->get('app_name');

        $email = (new TemplatedEmail())
            ->from(new Address($appNotifier, $appName))
            ->to($message->userEmail)
            ->subject($this->translator->trans('email.weekly_posts.subject'))
            ->htmlTemplate('emails/weekly_posts_notification.fr.html.twig')
            ->context([
                'weekly_posts' => $message->weeklyPosts,
                'userEmail' => $message->userEmail,
            ]);

        $this->mailer->send($email);
    }
}
