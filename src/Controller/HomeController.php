<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Utils\PostUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(name: 'app_home_')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/page/{alias:page}', name: 'page')]
    public function page(Page $page): Response
    {
        return $this->render('home/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{seoTypeSlug}/{id}/{titleSlug}', name: 'post', methods: ['GET'], requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG])]
    public function postById(Post $post, string $seoTypeSlug, string $titleSlug, PostLikeRepository $postLikeRepo, #[CurrentUser] ?User $user = null): Response
    {
        // URL manipulation, like changing the ID
        if ($titleSlug !== $post->getTitleSlug()) {
            return $this->redirectToRoute('app_home_post', [
                'seoTypeSlug' => $seoTypeSlug,
                'id' => $post->getId(),
                'titleSlug' => $post->getTitleSlug(),
            ]);
        }

        $likedPostIds = ($user instanceof User)
            ? $postLikeRepo->findLikedPostIdsByUser($post, $user)
            : [];

        return $this->render('home/post.html.twig', [
            'post' => $post,
            'likedPostIds' => $likedPostIds,
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG], condition: "service('post_utils').getValidSeoSlugs()")]
    public function postsByType(PostRepository $postRepo, PostLikeRepository $postLikeRepo, string $seoTypeSlug, #[CurrentUser] ?User $user = null): Response
    {
        if (null === $type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) {
            return $this->redirectToRoute('app_home_index');
        }

        $posts = $postRepo->findBy(['type' => $type], ['id' => 'DESC']);
        $likedPostIds = ($user instanceof User)
            ? $postLikeRepo->findLikedPostIdsByUser($posts, $user)
            : [];

        return $this->render('home/posts.html.twig', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
        ]);
    }
}
