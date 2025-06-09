<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile', name: 'app_profile_', priority: 3)]
final class ProfileController extends AbstractController
{
    #[Route('/{pseudo}', name: 'index')]
    public function index(UserRepository $userRepository, PostRepository $postRepository, string $pseudo): Response
    {
        if (null === $user = $userRepository->findOneBy(['pseudo' => $pseudo])) {
            throw new NotFoundHttpException('profile_not_found');
        }

        $posts = $postRepository->findBy(['user' => $user]);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'posts_count' => \count($posts),
        ]);
    }
}
