<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\PostDto;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\Wall;
use App\Enum\PostType;
use App\Form\ContactMemberForm;
use App\Service\UserInteraction;
use App\Utils\PostUtils;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface as CacheItemInterface;

#[Route(name: 'app_frontend_', methods: ['GET'], priority: 1)]
final class FrontendController extends AbstractController
{
    public const int PAGE_MAX_POSTS = 10;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserInteraction $userInteraction,
        private readonly PaginatorInterface $paginator,
        private readonly PostDto $postDto,
        #[Autowire(service: 'app.index_latest_posts')]
        private readonly CacheInterface $cache,
    ) {
    }

    #[Route('/', name: 'index')]
    // #[Cache(maxage: 86400, smaxage: 86400, public: true)]
    public function index(#[CurrentUser] ?User $currentUser): Response
    {
        $this->cache->delete('index_latest_posts');
        /*
        $newestPosts = $this->cache->get('index_latest_posts', function (CacheItemInterface $item) {
            $item->expiresAfter(null);
            $item->tag(['index_latest_posts']);

            return $this->em->getRepository(Post::class)->findNewest();
        });
        */

        $posts = $this->em->getRepository(Post::class)->fetchNewest($currentUser?->getId());

        return $this->render('frontend/index.html.twig', [
            'page' => $this->em->getRepository(Page::class)->findOneBy(['alias' => 'home']),
            'newest_posts' => $this->postDto->fromFlatEntities($posts),
        ]);
    }

    #[Route('/sw', name: 'sw')]
    public function sw(Request $request): Response
    {
        $stream = fopen('sw.txt', 'a+');
        \assert(false !== $stream);

        $code = '';
        $existingCodes = explode("\n", (string) file_get_contents('sw.txt'));

        if ($request->query->has('generate')) {
            $code = random_int(1, 9999);
            $code = str_pad((string) $code, 4, '0', \STR_PAD_LEFT);

            if (!\in_array($code, $existingCodes, true)) {
                fwrite($stream, $code."\n");
            }
        }

        return $this->render('frontend/sw.html.twig', [
            'code' => $code,
            'existing_codes' => $existingCodes,
        ]);
    }

    #[Route('/sendmail', name: 'send_mail')]
    public function sendMail(MailerInterface $mailer): Response
    {
        $email = new TemplatedEmail()
            ->from(new Address('mimo@gmail.com', 'bla'))
            ->to('mimo2@gmail.com')
            ->subject('bla sujet')
            ->text('body body');

        $mailer->send($email);

        return $this->redirectToRoute('app_frontend_index', status: Response::HTTP_SEE_OTHER);
    }

    #[Route('/membre/{pseudo:user}', name: 'member_profile')]
    public function memberProfile(User $user, Request $request): Response
    {
        if (!$user->isVerified()) {
            $this->addFlash('warning', 'flash.user_not_verified');

            return $this->redirectToRoute('app_frontend_index', status: Response::HTTP_SEE_OTHER);
        }

        $posts = $this->em->getRepository(Post::class)->fetchPaginatedByUser($user);
        $pagination = $this->paginator->paginate(
            $this->postDto->fromFlatEntities($posts),
            $request->query->getInt('page', 1),
            self::PAGE_MAX_POSTS,
        );

        return $this->render('frontend/member_profile.html.twig', [
            'form' => $this->createForm(ContactMemberForm::class),
            'member' => $user,
            'pagination' => $pagination,
            'posts_count' => $pagination->getTotalItemCount(),
            'comments_count' => $this->em->getRepository(UserComment::class)->count(['user' => $user]),
        ]);
    }

    #[Route('/unsubscribe', name: 'unsubscribe')]
    public function unsubscribe(): Response
    {
        return $this->redirectToRoute('app_user_edit');
    }

    #[Route('/page/{alias:page}', name: 'page')]
    public function page(Page $page): Response
    {
        return $this->render('frontend/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{seoTypeSlug}/{titleSlug}', name: 'post', requirements: ['seoTypeSlug' => PostUtils::SEO_POST_SLUGS, 'titleSlug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function post(string $titleSlug, #[CurrentUser] ?User $currentUser = null): Response
    {
        $post = $this->em->getRepository(Post::class)->fetchOne($titleSlug, $currentUser?->getId())
            ?? throw $this->createNotFoundException();

        return $this->render('frontend/post.html.twig', [
            'entity' => $this->postDto->fromFlatEntity($post),
        ]);
    }

    #[Route('/{seoTypeSlug}', name: 'posts', requirements: ['seoTypeSlug' => PostUtils::SEO_POST_SLUGS])]
    public function posts(Request $request, string $seoTypeSlug): Response
    {
        if (!($type = PostUtils::getTypeBySeoSlug($seoTypeSlug)) instanceof PostType) {
            return $this->redirectToRoute('app_frontend_index');
        }

        $posts = $this->em->getRepository(Post::class)->fetchByType($type);

        return $this->renderPaginatedEntities(
            $request,
            $this->postDto->fromFlatEntities($posts),
            'frontend/posts.html.twig',
        );
    }

    #[Route('/questions', name: 'questions')]
    public function questions(Request $request): Response
    {
        $posts = $this->em->getRepository(Post::class)->fetchQuestions();

        return $this->renderPaginatedEntities(
            $request,
            $this->postDto->fromFlatEntities($posts),
            'frontend/questions.html.twig',
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
            'posts' => $this->postDto->fromFlatEntities($posts),
        ]);
    }

    #[Route('/el7it/{id}', name: 'wall')]
    public function wallBricks(int $id, #[CurrentUser] ?User $currentUser = null): Response
    {
        $post = $this->em->getRepository(Wall::class)->fetchOne($id, $currentUser?->getId());

        return $this->render('frontend/wall.html.twig', [
            'entity' => $this->postDto->fromFlatEntity($post, 'wall'),
        ]);
    }

    #[Route('/el7it', name: 'walls')]
    public function wall(#[CurrentUser] ?User $currentUser = null): Response
    {
        $posts = $this->em->getRepository(Wall::class)->fetchAll($currentUser?->getId());

        return $this->render('frontend/walls.html.twig', [
            'entities' => $this->postDto->fromFlatEntities($posts, 'wall'),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $posts
     */
    private function renderPaginatedEntities(Request $request, array $posts, string $template): Response
    {
        $pagination = $this->paginator->paginate($posts, $request->query->getInt('page', 1), self::PAGE_MAX_POSTS);

        return $this->render($template, [
            'pagination' => $pagination,
        ]);
    }
}
