<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
