<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile', name: 'app_profile_', priority: 3)]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/{pseudo}', name: 'index')]
    public function index(UserRepository $userRepository, PostRepository $postRepository, string $pseudo): Response
    {
        if (null === $user = $this->em->getRepository(User::class)->findOneBy(['pseudo' => $pseudo])) {
            throw new NotFoundHttpException('profile_not_found');
        }

        $posts = $this->em->getRepository(Post::class)->findBy(['user' => $user]);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'posts_count' => \count($posts),
            'likedPostIds' => $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($posts, $user),
            'commentPostIds' => $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($posts, $user),
        ]);
    }
}
