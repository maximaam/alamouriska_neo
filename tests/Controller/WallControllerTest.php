<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Wall;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WallControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $wallRepository;
    private string $path = '/el7it/';

    protected function setUp(): void
    {
        self::markTestSkipped();

        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->wallRepository = $this->manager->getRepository(Wall::class);

        foreach ($this->wallRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        // $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wall index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        self::markTestIncomplete();
        $this->client->request('GET', \sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'wall[description]' => 'Testing',
            'wall[createdAt]' => 'Testing',
            'wall[updatedAt]' => 'Testing',
            'wall[User]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->wallRepository->count([]));
    }

    public function testShow(): void
    {
        self::markTestIncomplete();
        $fixture = new Wall();
        $fixture->setDescription('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUpdatedAt('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wall');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        self::markTestIncomplete();
        $fixture = new Wall();
        $fixture->setDescription('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setUser('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'wall[description]' => 'Something New',
            'wall[createdAt]' => 'Something New',
            'wall[updatedAt]' => 'Something New',
            'wall[User]' => 'Something New',
        ]);

        self::assertResponseRedirects('/wall/');

        $fixture = $this->wallRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
        self::assertSame('Something New', $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        self::markTestIncomplete();
        $fixture = new Wall();
        $fixture->setDescription('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUpdatedAt('Value');
        $fixture->setUser('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/wall/');
        self::assertSame(0, $this->wallRepository->count([]));
    }
}
