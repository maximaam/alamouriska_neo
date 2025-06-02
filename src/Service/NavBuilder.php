<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Enum\PostType;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class NavBuilder
{
    public function __construct(
        private FactoryInterface $factory,
        // private readonly EntityManager $entityManager,
        // private readonly RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {
    }

    public function mainMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav me-auto mb-2 mb-lg-0');

        foreach (PostType::cases() as $type) {
            $child = strtoupper($this->translator->trans(\sprintf('post.%s.plural', $type->name)));
            $menu->addChild($child, [
                'route' => 'app_home_posts',
                'routeParameters' => [
                    'seoTypeSlug' => $this->translator->trans(\sprintf('post.%s.seo_route', $type->name)),
                ],
                'attributes' => ['class' => 'nav-item'],
                'linkAttributes' => ['class' => 'nav-link'],
            ]);
        }

        return $menu;
    }

    /*
    public function footerMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'footer-nav list-unstyled text-right');
        $pages = $this->entityManager->getRepository(Post::class)->findBy(['embedded' => false]);

        foreach ($pages as $page) {
            $menu->addChild($page->getTitle(), [
                'route' => 'index_page',
                'attributes' => ['class' => ''],
                'linkAttributes' => ['class' => 'footer'],
                'routeParameters' => [
                    'alias' => '$page->getAlias()',
                ]
            ]);
        }

        return $menu;
    }
    */
}
