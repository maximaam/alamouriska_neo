<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\User;
use App\Form\PostCommentForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                'action' => 'dislike',
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
            'action' => 'like',
            'likes' => \count($post->getPostLikes()),
        ]);
    }

    #[Route('/post-comment/{id}/comment', name: 'post_comment', methods: ['GET', 'POST'])]
    public function postComment(#[CurrentUser] User $user, Request $request, Post $post, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse|Response
    {
        /*
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('like-post', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }
        */

        $form = $this->createForm(PostCommentForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment = (new PostComment())
                ->setPost($post)
                ->setUser($user)
                ->setComment($form->get('comment')->getData());

                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                return $this->json([
                    'status' => 'success',
                    'comment_item' => $this->renderView('partials/_post-comment-item.html.twig', [
                        'postComment' => $comment,
                    ]),
                    // Resend a fresh form, in case the previous contains errors
                    'form' => $this->renderView('partials/_post-comment-form.html.twig', [
                        'form' => $this->createForm(PostCommentForm::class)->createView(),
                        'post' => $post,
                        'with_comments' => false,
                    ]),
                ]);
            }

            return $this->json([
                'status' => 'validation_error',
                'form' => $this->renderView('partials/_post-comment-form.html.twig', [
                    'form' => $form->createView(),
                    'post' => $post,
                    'with_comments' => false,
                ]),
            ]);
        }

        return $this->render('partials/_post-comment-form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'with_comments' => true,
        ]);
    }

    #[Route('/post-comment/{id}/delete', name: 'post_comment_delete', methods: ['POST'])]
    public function postCommentDelete(Request $request, PostComment $postComment, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('comment-delete', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $this->entityManager->remove($postComment);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
        ]);
    }
}
