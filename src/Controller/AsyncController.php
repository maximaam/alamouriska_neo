<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostComment;
use App\Entity\PostLike;
use App\Entity\User;
use App\Form\ContactMemberForm;
use App\Form\PostCommentForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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

    #[Route('/post-like/{id}/like', name: 'post_like', methods: ['POST'])]
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

        $sumLikes = \count($post->getPostLikes());

        $hasLiked = $this->entityManager
            ->getRepository(PostLike::class)
            ->findOneBy(['user' => $user, 'post' => $post]);

        if ($hasLiked instanceof PostLike) {
            $this->entityManager->remove($hasLiked);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'action' => 'dislike',
                'likes' => $sumLikes > 0 ? --$sumLikes : 0,
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
            'likes' => ++$sumLikes,
        ]);
    }

    #[Route('/post-comment/{id}/comment', name: 'post_comment', methods: ['GET', 'POST'])]
    public function postComment(Request $request, Post $post, #[CurrentUser] ?User $user = null): JsonResponse|Response
    {
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
    #[IsGranted(User::ROLE_USER)]
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

    #[Route('/contact-member/{id}', name: 'contact_member', methods: ['POST'])]
    #[IsGranted(User::ROLE_USER)]
    public function contactMember(#[CurrentUser] User $user, User $member, Request $request, NormalizerInterface $normalizer, MailerInterface $mailer): JsonResponse
    {
        $form = $this->createForm(ContactMemberForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $appNotifier */
            $appNotifier = $this->getParameter('app_notifier_email');
            /** @var string $appName */
            $appName = $this->getParameter('app_name');

            $email = (new TemplatedEmail())
                ->from(new Address($appNotifier, $appName))
                ->to((string) $member->getEmail())
                ->subject($this->translator->trans('email.contact_member.subject'))
                ->htmlTemplate('emails/contact_member.fr.html.twig')
                ->context([
                    'sender' => $user,
                    'receiver' => $member,
                    'message' => $form->get('message')->getData(),
                ]);

            $mailer->send($email);

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
