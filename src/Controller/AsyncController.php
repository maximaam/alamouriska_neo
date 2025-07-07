<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLike;
use App\Form\ContactMemberForm;
use App\Form\PostCommentForm;
use App\Message\ContactMemberEmailMessage;
use App\Message\PostCommentEmailMessage;
use App\Utils\SocialMediaUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/async', name: 'app_async_', condition: 'request.isXmlHttpRequest()')]
final class AsyncController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/user-like/{id}/like', name: 'user_like', methods: ['POST'])]
    public function postLike(Request $request, Post $post, CsrfTokenManagerInterface $csrfTokenManager, #[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'redirect',
                'url' => $this->generateUrl('app_security_login'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('like-post', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $sumLikes = \count($post->getUserLikes());

        $hasLiked = $this->entityManager
            ->getRepository(UserLike::class)
            ->findOneBy(['user' => $user, 'post' => $post]);

        if ($hasLiked instanceof UserLike) {
            $this->entityManager->remove($hasLiked);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'action' => 'dislike',
                'likes' => $sumLikes > 0 ? --$sumLikes : 0,
            ]);
        }

        $postLike = (new UserLike())
            ->setPost($post)
            ->setUser($user);

        $this->entityManager->persist($postLike);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'action' => 'like',
            'likes' => ++$sumLikes,
        ]);
    }

    #[Route('/user-comment/{id}/comment', name: 'user_comment', methods: ['GET', 'POST'])]
    public function postComment(Request $request, Post $post, MessageBusInterface $messageBus, #[CurrentUser] ?User $user = null): JsonResponse|Response
    {
        $form = $this->createForm(PostCommentForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$user instanceof User) {
                    throw new \LogicException('Access denied.');
                }

                $commentData = SocialMediaUtils::linkifyUrls($form->get('comment')->getData());
                $userComment = (new UserComment())
                    ->setPost($post)
                    ->setUser($user)
                    ->setComment($commentData);

                $this->entityManager->persist($userComment);
                $this->entityManager->flush();

                if ($post->getUser() !== $user) {
                    $messageBus->dispatch(new PostCommentEmailMessage(
                        (string) $user->getPseudo(),
                        (string) $post->getUser()->getPseudo(),
                        (string) $post->getUser()->getEmail(),
                        (int) $post->getId(),
                        $post->getTitle().' | '.$post->getTitleArabic(),
                        $post->getType()->name,
                        $post->getTitleSlug()
                    ));
                }

                return $this->json([
                    'status' => 'success',
                    'comment_item' => $this->renderView('partials/_user-comment-item.html.twig', [
                        'userComment' => $userComment,
                    ]),
                    // Resend a fresh form, in case the previous contains errors
                    'form' => $this->renderView('partials/_user-comment-form.html.twig', [
                        'form' => $this->createForm(PostCommentForm::class)->createView(),
                        'post' => $post,
                    ]),
                ]);
            }

            return $this->json([
                'status' => 'validation_error',
                'form' => $this->renderView('partials/_user-comment-form.html.twig', [
                    'form' => $form->createView(),
                    'post' => $post,
                ]),
            ]);
        }

        return $this->render('partials/_user-comment-form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'with_comments' => true,
            'with_form_container' => true,
        ]);
    }

    #[Route('/post-comment/{id}/delete', name: 'post_comment_delete', methods: ['POST'])]
    #[IsGranted(User::ROLE_USER)]
    public function postCommentDelete(Request $request, UserComment $postComment, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
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

    #[Route('/contact-member/{id}', name: 'contact_member', methods: ['POST'])]
    #[IsGranted(User::ROLE_USER)]
    public function contactMember(#[CurrentUser] User $user, User $member, Request $request, NormalizerInterface $normalizer, MessageBusInterface $messageBus): JsonResponse
    {
        $form = $this->createForm(ContactMemberForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageBus->dispatch(new ContactMemberEmailMessage(
                (string) $user->getPseudo(),
                $form->get('message')->getData(),
                (string) $member->getPseudo(),
                (string) $member->getEmail(),
            ));

            return $this->json([
                'status' => 'success',
                'msg' => $this->translator->trans('flash.contact_member_email_sent'),
            ]);
        }

        return $this->json([
            'status' => 'error',
            'error' => $normalizer->normalize($form, null, ['groups' => ['Default']]),
        ]);
    }
}
