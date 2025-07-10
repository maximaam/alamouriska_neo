<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\Wall;
use App\Form\ContactMemberForm;
use App\Service\UserInteraction;
use App\Utils\PostUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(name: 'app_frontend_', methods: ['GET'], priority: 1)]
final class FrontendController extends AbstractController
{
    public const PAGE_MAX_POSTS = 10;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserInteraction $userInteraction,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(#[CurrentUser] ?User $user = null): Response
    {
        $latestPosts = $this->em->getRepository(Post::class)->findLatests();

        return $this->render('frontend/index.html.twig', [
            'page' => $this->em->getRepository(Page::class)->findOneBy(['alias' => 'home']),
            'newest_posts' => $latestPosts,
            ...$this->userInteraction->getUserInteractionIds($latestPosts, 'post', $user),
        ]);
    }

    #[Route('/membre/{pseudo:user}', name: 'member_profile')]
    public function memberProfile(User $user, Request $request, #[CurrentUser] ?User $currentUser = null): Response
    {
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'flash.user_not_verified');

            return $this->redirectToRoute('app_frontend_index', status: Response::HTTP_SEE_OTHER);
        }

        $pagination = $this->paginator->paginate(
            $this->em->getRepository(Post::class)->findPaginatedByUserQuery($user),
            $request->query->getInt('page', 1),
            self::PAGE_MAX_POSTS,
        );

        return $this->render('frontend/member_profile.html.twig', [
            'form' => $this->createForm(ContactMemberForm::class),
            'member' => $user,
            'pagination' => $pagination,
            'posts_count' => $pagination->getTotalItemCount(),
            'comments_count' => $this->em->getRepository(UserComment::class)->count(['user' => $user]),
            ...$this->userInteraction->getUserInteractionIds($pagination->getItems(), 'post', $currentUser),
        ]);
    }

    #[Route('/page/{alias:page}', name: 'page')]
    public function page(Page $page): Response
    {
        return $this->render('frontend/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{seoTypeSlug}/{id}/{titleSlug}', name: 'post', methods: ['GET'], requirements: ['seoTypeSlug' => PostUtils::SEO_POST_SLUGS, 'id' => Requirement::POSITIVE_INT, 'titleSlug' => Requirement::ASCII_SLUG])]
    // condition: "service('post_utils').isValidSlug('mots-algeriens')",
    public function post(Post $post, string $seoTypeSlug, string $titleSlug, #[CurrentUser] ?User $currentUser = null): Response
    {
        // URL manipulation, like changing the ID
        if ($titleSlug !== $post->getTitleSlug()) {
            return $this->redirectToRoute('app_frontend_post', [
                'seoTypeSlug' => $seoTypeSlug,
                'id' => $post->getId(),
                'titleSlug' => $post->getTitleSlug(),
            ]);
        }

        return $this->render('frontend/post.html.twig', [
            'entity' => $post,
            ...$this->userInteraction->getUserInteractionIds($post, 'post', $currentUser),
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => PostUtils::SEO_POST_SLUGS])]
    public function posts(Request $request, string $seoTypeSlug, #[CurrentUser] ?User $currentUser = null): Response
    {
        if (null === $type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) {
            return $this->redirectToRoute('app_frontend_index');
        }

        return $this->renderPaginatedEntities(
            $request,
            $this->em->getRepository(Post::class)->findPaginatedQuery($type),
            'frontend/posts.html.twig',
            $currentUser,
        );
    }

    #[Route('/questions', name: 'questions')]
    public function questions(Request $request, #[CurrentUser] ?User $currentUser = null): Response
    {
        return $this->renderPaginatedEntities(
            $request,
            $this->em->getRepository(Post::class)->findPaginatedQuestionsQuery(),
            'frontend/questions.html.twig',
            $currentUser,
        );
    }

    #[Route('/recherche', name: 'search')]
    public function search(Request $request, #[CurrentUser] ?User $currentUser = null): Response
    {
        $searchInput = $request->query->getString('q');
        $searchLen = \strlen($searchInput);

        if ($searchLen <= 3 || $searchLen > 100) {
            return $this->redirectToRoute('app_frontend_index');
        }

        $posts = $this->em->getRepository(Post::class)->search($searchInput);

        return $this->render('frontend/search.html.twig', [
            'search_input' => $searchInput,
            'posts' => $posts,
            ...$this->userInteraction->getUserInteractionIds($posts, 'post', $currentUser),
        ]);
    }

    #[Route('/el7it/{id}', name: 'wall')]
    public function wallBricks(Wall $wall, #[CurrentUser] ?User $currentUser = null): Response
    {
        return $this->render('frontend/wall.html.twig', [
            'entity' => $wall,
            ...$this->userInteraction->getUserInteractionIds($wall, 'wall', $currentUser),
        ]);
    }

    #[Route('/el7it', name: 'walls')]
    public function wall(#[CurrentUser] ?User $currentUser = null): Response
    {
        $walls = $this->em->getRepository(Wall::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('frontend/walls.html.twig', [
            'entities' => $walls,
            ...$this->userInteraction->getUserInteractionIds($walls, 'wall', $currentUser),
        ]);
    }

    /** 
     * @param Query<mixed> $query 
     */
    private function renderPaginatedEntities(Request $request, Query $query, string $template, ?User $user): Response
    {
        $pagination = $this->paginator->paginate($query, $request->query->getInt('page', 1), self::PAGE_MAX_POSTS);

        return $this->render($template, [
            'pagination' => $pagination,
            ...$this->userInteraction->getUserInteractionIds($pagination->getItems(), 'post', $user),
        ]);
    }
}
