<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Utils\PostUtils;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(name: 'app_frontend_', priority: -10)]
final class FrontendController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(PageRepository $pageRepo): Response
    {
        return $this->render('frontend/index.html.twig', [
            'page' => $pageRepo->findOneBy(['alias' => 'home']),
        ]);
    }

    #[Route('/membre/{pseudo}', name: 'member_profile')]
    public function memberProfile(string $pseudo): Response
    {
        if (null === $member = $this->em->getRepository(User::class)->findOneBy(['pseudo' => $pseudo])) {
            throw new NotFoundHttpException('profile_not_found');
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['user' => $member]);

        return $this->render('frontend/member_profile.html.twig', [
            'member' => $member,
            'posts' => $posts,
            'comments_count' => $this->em->getRepository(PostComment::class)->count(['user' => $member]),
            'likedPostIds' => $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($posts, $member),
            'commentPostIds' => $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($posts, $member),
        ]);
    }

    #[Route('/page/{alias:page}', name: 'page')]
    public function page(Page $page): Response
    {
        return $this->render('frontend/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{seoTypeSlug}/{id}/{titleSlug}', name: 'post', methods: ['GET'], requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG])]
    public function postById(Post $post, string $seoTypeSlug, string $titleSlug, PostLikeRepository $postLikeRepo, PostCommentRepository $postCommentRepo, #[CurrentUser] ?User $user = null): Response
    {
        // URL manipulation, like changing the ID
        /*
        if ($titleSlug !== $post->getTitleSlug()) {
            return $this->redirectToRoute('app_frontend_post', [
                'seoTypeSlug' => $seoTypeSlug,
                'id' => $post->getId(),
                'titleSlug' => $post->getTitleSlug(),
            ]);
        }
            */

        $likedPostIds = ($user instanceof User)
            ? $postLikeRepo->findLikedPostIdsByUser($post, $user)
            : [];

        $commentPostIds = ($user instanceof User)
            ? $postCommentRepo->findCommentPostIdsByUser($post, $user)
            : [];

        return $this->render('frontend/post.html.twig', [
            'post' => $post,
            'likedPostIds' => $likedPostIds,
            'commentPostIds' => $commentPostIds,
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG], condition: "service('post_utils').getValidSeoSlugs()")]
    public function postsByType(PaginatorInterface $paginator, Request $request, PostRepository $postRepo, PostLikeRepository $postLikeRepo, PostCommentRepository $postCommentRepo, string $seoTypeSlug, #[CurrentUser] ?User $user = null): Response
    {
        if (null === $type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) {
            return $this->redirectToRoute('app_frontend_index');
        }

        $pagination = $paginator->paginate(
            $postRepo->findPaginatedQuery($type),
            $request->query->getInt('page', 1),
            3
        );

        /** @var Post[] $posts */
        $posts = $pagination->getItems();

        $likedPostIds = ($user instanceof User)
            ? $postLikeRepo->findLikedPostIdsByUser($posts, $user)
            : [];

        $commentPostIds = ($user instanceof User)
            ? $postCommentRepo->findCommentPostIdsByUser($posts, $user)
            : [];

        return $this->render('frontend/posts.html.twig', [
            'pagination' => $pagination,
            'likedPostIds' => $likedPostIds,
            'commentPostIds' => $commentPostIds,
        ]);
    }
}
