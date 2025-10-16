<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\Wall;
use App\Enum\PostType;
use App\Helper\EntityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EntityHelperTest extends WebTestCase
{
    private EntityManagerInterface $manager;
    private EntityHelper $entityHelper;
    private User $user;

    protected function setUp(): void
    {
        $this->manager = self::getContainer()->get('doctrine')->getManager();
        $this->entityHelper = self::getContainer()->get(EntityHelper::class);

        foreach ($this->manager->getRepository(User::class)->findAll() as $user) {
            $this->manager->remove($user);
        }

        foreach ($this->manager->getRepository(Post::class)->findAll() as $post) {
            $this->manager->remove($post);
        }

        $this->manager->flush();

        $this->user = $this->createUser();
    }

    public function testResolveEntitySuccess(): void
    {
        $post = (new Post())
            ->setType(PostType::word)
            ->setTitle('the title')
            ->setDescription('the descp')
            ->setUser($this->user);

        $this->manager->persist($post);
        $this->manager->flush();

        $post = $this->manager->getRepository(Post::class)->findOneBy(['title' => 'the title']);
        $entity = $this->entityHelper->resolveEntity($this->manager, 'post', $post->getId());

        self::assertSame('the title', $entity->getTitle());
    }

    public function testGenerateEntityUrlForPost(): void
    {
        $post = (new Post())
            ->setType(PostType::word)
            ->setTitle('Ba3ouk fi souk D"lala')
            ->setDescription('the descp')
            ->setUser($this->user);

        $this->manager->persist($post);
        $this->manager->flush();

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        $translator = self::getContainer()->get(TranslatorInterface::class);
        $url = $this->entityHelper->generateEntityUrl($post, $urlGenerator, $translator);

        self::assertSame(
            \sprintf('https://www.alamouriska.com/mots-algeriens/%s/ba3ouk-fi-souk-d-lala', $post->getId()),
            $url,
        );
    }

    public function testGenerateEntityUrlForWall(): void
    {
        $wall = (new Wall())
            ->setDescription('the descp')
            ->setDescriptionHtml('the descp')
            ->setUser($this->user);

        $this->manager->persist($wall);
        $this->manager->flush();

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        $translator = self::getContainer()->get(TranslatorInterface::class);
        $url = $this->entityHelper->generateEntityUrl($wall, $urlGenerator, $translator);

        self::assertSame(
            \sprintf('https://www.alamouriska.com/el7it/%s', $wall->getId()),
            $url,
        );
    }

    public function testCreateUserComment(): void
    {
        $wall = (new Wall())
            ->setDescription('the descp 2')
            ->setDescriptionHtml('the descp 2')
            ->setUser($this->user);

        $this->manager->persist($wall);
        $this->manager->flush();

        $userComment = $this->entityHelper->createUserComment($wall, $this->user, 'tchik http://www.alamouriska.com tchbila');

        self::assertInstanceOf(UserComment::class, $userComment);
        self::assertEquals('tchik <a href="http://www.alamouriska.com" target="_blank" rel="noopener noreferrer">http://www.alamouriska.com</a> tchbila', $userComment->getComment());
        self::assertInstanceOf(Wall::class, $userComment->getWall());
    }

    public function testCollectCommentators(): void
    {
        $wall = (new Wall())
            ->setDescription('the descp 3')
            ->setDescriptionHtml('the descp 3')
            ->setUser($this->user);

        $this->manager->persist($wall);
        $this->manager->flush();

        $commentators = $this->entityHelper->collectCommentators($wall, $this->user);

        self::assertIsArray($commentators);
        self::assertCount(0, $commentators); // No comments, only entity user
    }

    private function createUser(): User
    {
        $user = (new User())
            ->setEmail('email@gmail.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('qqqqqq')
            ->setIsVerified(true)
            ->setPseudo('mimosa');

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}
