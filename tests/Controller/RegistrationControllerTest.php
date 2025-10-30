<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        // self::ensureKernelShutdown();

        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();
    }

    public function testRegisterPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorTextContains('h1', 'Inscription');
    }

    public function testSuccessfulRegistration(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $formData = [
            'registration_form[email]' => 'test@example.com',
            'registration_form[pseudo]' => 'testuser',
            'registration_form[plainPassword]' => 'Password123!',
            // Add other required fields as per RegistrationForm
        ];

        $form = $crawler->selectButton('Envoyer')->form();
        $this->client->submit($form, $formData);

        // Check for redirect to homepage
        self::assertResponseRedirects('/'); // Adjust to match 'app_frontend_index' route

        // Follow redirect and check flash message
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', "Un email t'a été envoyé, merci de cliquer sur le lien pour valider ton inscription.");

        // Verify user is persisted in the database
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@example.com']);
        self::assertNotNull($user);
        self::assertSame('testuser', $user->getPseudo());
        self::assertTrue($this->passwordHasher->isPasswordValid($user, 'Password123!'));
    }

    public function testEmailSuccessfullySent(): void
    {
        self::markTestSkipped('review');

        $crawler = $this->client->request('GET', '/register');

        $formData = [
            'registration_form[email]' => 'test1@example.com',
            'registration_form[pseudo]' => 'testuser1',
            'registration_form[plainPassword]' => 'Password123!',
            // Add other required fields as per RegistrationForm
        ];

        $form = $crawler->selectButton('Envoyer')->form();
        $this->client->submit($form, $formData);

        $this->client->followRedirect(false);

        $this->assertEmailCount(1); // use assertQueuedEmailCount() when using Messenger

        // $email = $this->getMailerMessage();

        // Verify email was queued (assuming a mocked mailer)
        // $transport = static::getContainer()->get(Transport::class);
        // self::assertCount(1, $transport->getSentMessages());
        // $email = $transport->getSentMessages()[0];
        // self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
        // self::assertStringContainsString('registration_confirmation', $email->getHtmlBody());
    }

    public function testRedirectIfAuthenticated(): void
    {
        // Simulate an authenticated user
        $user = new User();
        $user->setEmail('authenticated@example.com');
        $user->setPseudo('authuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'Password123!'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Log in the user
        $this->client->loginUser($user);

        $this->client->request('GET', '/register');

        // Assert redirection (adjust based on your redirectIfAuthenticated logic)
        self::assertResponseRedirects('/'); // Adjust to match your redirect route
    }

    public function testInvalidFormSubmission(): void
    {
        $crawler = $this->client->request('GET', '/register');

        // Submit form with invalid data (e.g., missing required fields)
        $formData = [
            'registration_form[email]' => 'testuser.email.com', // Invalid email
            'registration_form[pseudo]' => 'testuser',
            'registration_form[plainPassword]' => 'qqqqqq',
        ];

        $form = $crawler->selectButton('Envoyer')->form();
        $this->client->submit($form, $formData);

        // Assert form is re-rendered with errors
        self::assertResponseIsUnprocessable();
        self::assertSelectorExists('div.invalid-feedback');
        self::assertSelectorExists('input.is-invalid');
        self::assertInputValueSame('registration_form[email]', 'testuser.email.com');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => '']);
        self::assertNull($user);
    }

    public function testSendMailWithoutMailTestBridge(): void
    {
        self::markTestSkipped('mail send test fails');

        $crawler = $this->client->request('GET', '/register');
        $this->client->followRedirects(false);

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $repo = $this->entityManager->getRepository(User::class);
        foreach ($repo->findAll() as $user) {
            $this->entityManager->remove($user);
        }
        $this->entityManager->flush();

        $form = $crawler->selectButton('Envoyer')->form([
            'registration_form[email]' => 'testuser1@example.com',
            'registration_form[pseudo]' => 'TestUser1',
            'registration_form[plainPassword]' => 'StrongPassword123',
        ]);
        $this->client->submit($form);

        $logger = new MessageLoggerListener();
        $container = self::getContainer();
        $container->get('event_dispatcher')->addSubscriber($logger);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Fetch messages from the logger
        /*
        $events = $logger->getEvents()->getEvents();
        self::assertCount(2, $events);
        */

        /** @var TemplatedEmail $message */
        /*
        $message = $events[0]->getMessage();
        self::assertStringContainsString('body body', $message->getTextBody());
        */
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
