<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Post;
use App\Entity\User;
use App\Message\WeeklyPostsNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:notify',
    description: 'Notify users about previous week posts.',
)]
class NotifyCommand extends Command
{
    private const ACTION_WEEKLY_POSTS = 'weeklyPosts';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'The notification action (e.g. "weeklyPosts")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        $validActions = [
            self::ACTION_WEEKLY_POSTS,
        ];

        if (!\in_array($action, $validActions, true)) {
            throw new \RuntimeException(\sprintf(
                'Invalid action "%s". Valid actions are: %s',
                $action,
                implode(', ', $validActions)
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
