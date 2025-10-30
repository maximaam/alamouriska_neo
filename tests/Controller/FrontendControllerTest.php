<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

final class FrontendControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->createPage();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Alamouriska ! Le site du parler algérien en derja (درجة)');
        self::assertSelectorCount(1, 'h1');
        self::assertSelectorTextContains('h1', 'Alamouriska ! Le site du parler algérien en derja (درجة)');
        // self::assertSelectorTextContains('.page-content', 'Welcome to the homepage.');
    }

    public function testDdd(): void
    {
        self::markTestSkipped('review');

        // Make the request
        $this->client->request('GET', '/sendmail');
        $this->client->followRedirects(false);

        // ✅ Expect a redirect to your index route
        self::assertResponseRedirects('/'); // adjust to actual route path if needed

        // ✅ Follow redirect to make sure it resolves correctly

        // self::assertResponseIsSuccessful();

        // ✅ Check that exactly one email was sent
        $this->assertEmailCount(1);

        // ✅ Fetch the sent email
        $email = $this->getMailerMessage();

        // ✅ Assertions on its contents
        $this->assertEmailHeaderSame($email, 'To', 'mimo2@gmail.com');
        $this->assertEmailTextBodyContains($email, 'body body');
        $this->assertEmailHeaderSame($email, 'Subject', 'bla sujet');
    }

    public function testSendMailWithoutMailTestBridge(): void
    {
        $container = self::getContainer();
        $logger = new MessageLoggerListener();
        $container->get('event_dispatcher')->addSubscriber($logger);

        $this->client->request('GET', '/sendmail');
        $this->client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Fetch messages from the logger
        // $events = $logger->getEvents()->getEvents();
        // self::assertCount(2, $events);

        /** @var TemplatedEmail $message */
        // $message = $events[0]->getMessage();
        // self::assertStringContainsString('body body', $message->getTextBody());
    }

    public function testMailerWorks(): void
    {
        $mailer = self::getContainer()->get('mailer');
        $email = (new Email())
            ->from('test@example.com')
            ->to('me@example.com')
            ->subject('Test')
            ->text('Hello world');
        $mailer->send($email);

        self::assertEmailCount(1);

        /** @var RawMessage $email */
        $email = $this->getMailerMessage(0);
        self::assertEmailTextBodyContains($email, 'Hello world');
    }

    private function createPage(): void
    {
        $repo = $this->em->getRepository(Page::class);

        // Remove any existing users from the test database
        foreach ($repo->findAll() as $page) {
            $this->em->remove($page);
        }

        $this->em->flush();

        $page = new Page();
        $page->setTitle('Home');
        $page->setAlias('home');
        $page->setDescription('Welcome to the homepage.');

        $this->em->persist($page);
        $this->em->flush();
    }
}
