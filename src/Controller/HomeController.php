<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Utils\PostUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(name: 'app_home_')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PostRepository $postRepo): Response
    {
        return $this->render('home/index.html.twig', [
        ]);
    }

    #[Route('/page/{alias:page}', name: 'page')]
    public function page(Page $page): Response
    {
        return $this->render('home/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{seoTypeSlug}/{id}/{titleSlug}', name: 'post', methods: ['GET'], requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG])]
    public function postById(Post $post, string $seoTypeSlug, string $titleSlug): Response
    {
        // URL manipulation, like changing the ID
        if ($titleSlug !== $post->getTitleSlug()) {
            return $this->redirectToRoute('app_home_post', [
                'seoTypeSlug' => $seoTypeSlug,
                'id' => $post->getId(),
                'titleSlug' => $post->getTitleSlug(),
            ]);
        }

        return $this->render('home/post.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG], condition: "service('post_utils').getValidSeoSlugs()")]
    public function postsByType(PostRepository $postRepo, string $seoTypeSlug): Response
    {
        if (null === $type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) {
            return $this->redirectToRoute('app_home_index');
        }

        return $this->render('home/posts.html.twig', [
            'posts' => $postRepo->findBy(['type' => $type], ['id' => 'DESC']),
        ]);
    }
}
