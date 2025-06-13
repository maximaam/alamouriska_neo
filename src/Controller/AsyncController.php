<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/async', name: 'app_async_', condition: 'request.isXmlHttpRequest()')]
final class AsyncController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/post-like/{id}/like', name: 'post_like', methods: ['POST'])]
    public function postLike(#[CurrentUser] User $user, Request $request, Post $post, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('like-post', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $hasLiked = $this->entityManager
            ->getRepository(PostLike::class)
            ->findOneBy(['user' => $user, 'post' => $post]);

        if ($hasLiked instanceof PostLike) {
            $this->entityManager->remove($hasLiked);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'likes' => \count($post->getPostLikes()),
            ]);
        }

        $postLike = (new PostLike())
            ->setPost($post)
            ->setUser($user);

        $this->entityManager->persist($postLike);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'likes' => \count($post->getPostLikes()),
        ]);
    }
}
