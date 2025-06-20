<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\User;
use App\Form\ContactMemberForm;
use App\Repository\PageRepository;
use App\Utils\PostUtils;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(name: 'app_frontend_', priority: 1)]
final class FrontendController extends AbstractController
{
    public const PAGE_MAX_POSTS = 3;

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

    #[Route('/membre/{pseudo:user}', name: 'member_profile')]
    public function memberProfile(User $user): Response
    {
        $form = $this->createForm(ContactMemberForm::class);
        $posts = $this->em->getRepository(Post::class)->findBy(['user' => $user]);

        return $this->render('frontend/member_profile.html.twig', [
            'form' => $form,
            'member' => $user,
            'posts' => $posts,
            'comments_count' => $this->em->getRepository(PostComment::class)->count(['user' => $user]),
            'likedPostIds' => $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($posts, $user),
            'commentPostIds' => $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($posts, $user),
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
    public function postById(Post $post, string $seoTypeSlug, string $titleSlug, #[CurrentUser] ?User $user = null): Response
    {
        // URL manipulation, like changing the ID
        if ($titleSlug !== $post->getTitleSlug()) {
            return $this->redirectToRoute('app_frontend_post', [
                'seoTypeSlug' => $seoTypeSlug,
                'id' => $post->getId(),
                'titleSlug' => $post->getTitleSlug(),
            ]);
        }

        $likedPostIds = ($user instanceof User)
            ? $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($post, $user)
            : [];

        $commentPostIds = ($user instanceof User)
            ? $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($post, $user)
            : [];

        return $this->render('frontend/post.html.twig', [
            'post' => $post,
            'likedPostIds' => $likedPostIds,
            'commentPostIds' => $commentPostIds,
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => Requirement::ASCII_SLUG], condition: "service('post_utils').getValidSeoSlugs()")]
    public function postsByType(PaginatorInterface $paginator, Request $request, string $seoTypeSlug, #[CurrentUser] ?User $user = null): Response
    {
        if (null === $type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) {
            return $this->redirectToRoute('app_frontend_index');
        }

        $pagination = $paginator->paginate(
            $this->em->getRepository(Post::class)->findPaginatedQuery($type),
            $request->query->getInt('page', 1),
            self::PAGE_MAX_POSTS,
        );

        /** @var Post[] $posts */
        $posts = $pagination->getItems();

        $likedPostIds = ($user instanceof User)
            ? $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($posts, $user)
            : [];

        $commentPostIds = ($user instanceof User)
            ? $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($posts, $user)
            : [];

        return $this->render('frontend/posts.html.twig', [
            'pagination' => $pagination,
            'likedPostIds' => $likedPostIds,
            'commentPostIds' => $commentPostIds,
        ]);
    }
}
