<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Entity\User;
use App\Message\WeeklyPostsNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:notify',
    description: 'Notify users about previous week posts.',
)]
readonly class NotifyCommand
{
    private const ACTION_WEEKLY_POSTS = 'weeklyPosts';
    private const VALID_ACTIONS = [
        self::ACTION_WEEKLY_POSTS,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(
        #[Argument(description: 'The notification action (e.g. "weeklyPosts")')]
        string $action,
        SymfonyStyle $io
    ): int {
        if (!\in_array($action, self::VALID_ACTIONS, true)) {
            throw new \RuntimeException(\sprintf(
                'Invalid action "%s". Valid actions are: %s',
                $action,
                implode(', ', self::VALID_ACTIONS)
            ));
        }

        $io->info(\sprintf('Starting %s...', $action));

        match ($action) {
            self::ACTION_WEEKLY_POSTS => $this->sendWeeklyPosts($io),
        };

        $io->success(\sprintf('Successfully ended %s...', $action));

        return Command::SUCCESS;
    }

    private function sendWeeklyPosts(SymfonyStyle $io): void
    {
        $posts = $this->em->getRepository(Post::class)->countWeeklyPosts();

        if ([] === $posts) {
            $io->warning('No new posts this week.');

            return;
        }

        $userEmails = $this->em->getRepository(User::class)->findEnablePostNotification();

        $weeklyData = [];
        foreach ($posts as $post) {
            $weeklyData[$post['type']->name] = $post['count'];
        }

        foreach ($userEmails as $email) {
            $this->messageBus->dispatch(new WeeklyPostsNotificationMessage(
                $weeklyData,
                $email,
            ));
        }
    }
}
