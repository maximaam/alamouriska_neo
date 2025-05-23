<?php

declare(strict_types=1);

namespace App\Service;

use Knp\Menu\FactoryInterface;
use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Enum\PostType;

final class NavBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly EntityManager $entityManager,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ){
    }

    public function mainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'navbar-nav me-auto mb-2 mb-lg-0');

        foreach (PostType::cases() as $type) {
            $menu->addChild(strtoupper($this->translator->trans(sprintf('post.%s.plural', $type->name))), [
                'route' => 'app_home_index',
                'routeParameters' => [
                    'type' => $this->translator->trans(sprintf('post.%s.seo_route', $type->name))
                ],
                'attributes' => ['class' => 'nav-item'],
                'linkAttributes' => ['class' => 'nav-link'],
            ]);
        }

        //Set current for sub items
        /*
        $uri = $this->requestStack->getCurrentRequest()->getRequestUri();
        switch (true) {
            case strpos($uri, 'mots'):
                $menu->getChild('Mots')->setCurrent(true);
                break;
            case strpos($uri, 'expressions'):
                $menu->getChild('Expressions')->setCurrent(true);
                break;
            case strpos($uri, 'proverbes'):
                $menu->getChild('Proverbes')->setCurrent(true);
                break;
            case strpos($uri, 'blagues'):
                $menu->getChild('Blagues')->setCurrent(true);
                break;
            case strpos($uri, 'blogs'):
                $menu->getChild('Blogs')->setCurrent(true);
                break;

            default:
                $menu->setCurrent(true);
        }
        */

        return $menu;
    }

    public function footerMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'footer-nav list-unstyled text-right');
        //$pages = $this->entityManager->getRepository(Post::class)->findBy(['embedded' => false]);
        $pages = [];

        foreach ($pages as $page) {
            $menu->addChild($page->getTitle(), [
                'route' => 'index_page',
                'attributes' => ['class' => ''],
                'linkAttributes' => ['class' => 'footer'],
                'routeParameters' => [
                    'alias' => $page->getAlias(),
                ]
            ]);
        }

        return $menu;
    }
}