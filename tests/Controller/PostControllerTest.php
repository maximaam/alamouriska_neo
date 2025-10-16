<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Enum\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PostControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $postRepository;
    private string $path = '/post/';

    protected function setUp(): void
    {
        self::markTestSkipped('must be revisited.');

        $this->client = self::createClient();
        $this->manager = self::getContainer()->get('doctrine')->getManager();
        $this->postRepository = $this->manager->getRepository(Post::class);

        foreach ($this->postRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        // $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        self::markTestSkipped('must be revisited.');
        $this->client->request('GET', \sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'post[type]' => 'Testing',
            'post[title]' => 'Testing',
            'post[description]' => 'Testing',
            'post[user]' => 'Testing',
            'post[image]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->postRepository->count([]));
    }

    public function testShow(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new Post();
        // $fixture->setType(PostType::fromName('word')->value);
        $fixture->setTitle('My Title');
        $fixture->setDescription('My Title');
        // $fixture->setUser('My Title');
        // $fixture->setImage('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Post');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new Post();
        // $fixture->setType('Value');
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        // $fixture->setUser('Value');
        // $fixture->setImage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'post[type]' => 'Something New',
            'post[title]' => 'Something New',
            'post[description]' => 'Something New',
            'post[user]' => 'Something New',
            'post[image]' => 'Something New',
        ]);

        self::assertResponseRedirects('/post/');

        $fixture = $this->postRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getUser());
        self::assertSame('Something New', $fixture[0]->getImage());
    }

    public function testRemove(): void
    {
        self::markTestSkipped('must be revisited.');
        $fixture = new Post();
        // $fixture->setType('Value');
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        // $fixture->setUser('Value');
        // $fixture->setImage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', \sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/post/');
        self::assertSame(0, $this->postRepository->count([]));
    }
}
