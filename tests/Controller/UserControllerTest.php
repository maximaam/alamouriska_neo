<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $userRepository;
    private string $path = '/user/';

    protected function setUp(): void
    {
        self::markTestSkipped('must be revisited.');

        $this->client = self::createClient();
        $this->manager = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->manager->getRepository(User::class);

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        // $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        self::markTestSkipped('must be revisited.');
        $this->client->request('GET', \sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[email]' => 'Testing',
            'user[roles]' => 'Testing',
            'user[password]' => 'Testing',
            'user[isVerified]' => 'Testing',
            'user[pseudo]' => 'Testing',
            'user[enableCommunityContact]' => 'Testing',
            'user[enablePostNotification]' => 'Testing',
            'user[avatar]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->userRepository->count([]));
    }

    public function testShow(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles([]);
        $fixture->setPassword('My Title');
        $fixture->setIsVerified('My Title');
        $fixture->setPseudo('My Title');
        $fixture->setEnableCommunityContact('My Title');
        $fixture->setEnablePostNotification('My Title');
        $fixture->setAvatarName(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new User();
        $fixture->setEmail('Value');
        $fixture->setRoles([]);
        $fixture->setPassword('Value');
        $fixture->setIsVerified('Value');
        $fixture->setPseudo('Value');
        $fixture->setEnableCommunityContact('Value');
        $fixture->setEnablePostNotification('Value');
        $fixture->setAvatarName(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'Something New',
            'user[roles]' => 'Something New',
            'user[password]' => 'Something New',
            'user[isVerified]' => 'Something New',
            'user[pseudo]' => 'Something New',
            'user[enableCommunityContact]' => 'Something New',
            'user[enablePostNotification]' => 'Something New',
            'user[avatar]' => 'Something New',
        ]);

        self::assertResponseRedirects('/user/');

        $fixture = $this->userRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getRoles());
        self::assertSame('Something New', $fixture[0]->getPassword());
        self::assertSame('Something New', $fixture[0]->getIsVerified());
        self::assertSame('Something New', $fixture[0]->getDisplayName());
        self::assertSame('Something New', $fixture[0]->getEnableCommunityContact());
        self::assertSame('Something New', $fixture[0]->getEnablePostNotification());
        self::assertSame('Something New', $fixture[0]->getAvatar());
    }

    public function testRemove(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new User();
        $fixture->setEmail('Value');
        $fixture->setRoles([]);
        $fixture->setPassword('Value');
        $fixture->setIsVerified('Value');
        $fixture->setPseudo('Value');
        $fixture->setEnableCommunityContact('Value');
        $fixture->setEnablePostNotification('Value');
        $fixture->setAvatarName(null);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/user/');
        self::assertSame(0, $this->userRepository->count([]));
    }
}
