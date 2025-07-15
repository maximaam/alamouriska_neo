<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserComment;
use App\Entity\UserLike;
use App\Entity\Wall;
use App\Form\ContactMemberForm;
use App\Form\PostCommentForm;
use App\Message\ContactMemberEmailMessage;
use App\Message\UserCommentEmailMessage;
use App\Helper\EntityHelper;
use App\Utils\SocialMediaUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityHelper $entityHelper,
    ) {
    }

    #[Route('/user-like/{entityName}/{id}/like', name: 'user_like', methods: ['POST'])]
    public function postLike(Request $request, string $entityName, int $id, CsrfTokenManagerInterface $csrfTokenManager, #[CurrentUser] ?User $user = null): JsonResponse
    {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'redirect',
                'url' => $this->generateUrl('app_security_login'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('user-interactions', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $entity = $this->entityHelper->resolveEntity($this->em, EntityHelper::ENTITY_CLASS_MAP[$entityName], $id);
        if (null === $entity) {
            return $this->json(['error' => 'Invalid entity type or not found.'], 400);
        }

        $sumLikes = \count($entity->getUserLikes());

        $hasLiked = $this->em
            ->getRepository(UserLike::class)
            ->findOneBy(['user' => $user, $entityName => $entity]);

        if ($hasLiked instanceof UserLike) {
            $this->em->remove($hasLiked);
            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'action' => 'dislike',
                'likes' => $sumLikes > 0 ? --$sumLikes : 0,
            ]);
        }

        $userLike = (new UserLike())->setUser($user);
        if ($entity instanceof Post) {
            $userLike->setPost($entity);
        } else {
            $userLike->setWall($entity);
        }

        $this->em->persist($userLike);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'action' => 'like',
            'likes' => ++$sumLikes,
        ]);
    }

    #[Route('/user-comment/{entityName}/{id}/comment', name: 'user_comment', methods: ['GET', 'POST'])]
    public function postComment(Request $request, string $entityName, int $id, MessageBusInterface $messageBus, #[CurrentUser] ?User $user = null): JsonResponse|Response
    {
        $entity = $this->entityHelper->resolveEntity($this->em, EntityHelper::ENTITY_CLASS_MAP[$entityName], $id);
        if (null === $entity) {
            return $this->json(['error' => 'Invalid entity type or not found.'], 400);
        }

        $form = $this->createForm(PostCommentForm::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->render('partials/_user-comment-form.html.twig', [
                'entity' => $entity,
                'form' => $form->createView(),
                'with_comments' => true,
                'with_form_container' => true,
            ]);
        }

        if (!$form->isValid()) {
            return $this->json([
                'status' => 'validation_error',
                'form' => $this->renderCommentForm($form, $entity),
            ]);
        }

        if (!$user instanceof User) {
            throw new \LogicException('Access denied.');
        }

        $userComment = $this->entityHelper->createUserComment($entity, $user, $form->get('comment')->getData());
        $this->em->persist($userComment);
        $this->em->flush();

        $messageBus->dispatch(new UserCommentEmailMessage(
            (string) $user->getPseudo(),
            $this->entityHelper->collectCommentators($entity, $user),
            $this->entityHelper->generateEntityUrl($entity, $this->urlGenerator, $this->translator),
        ));

        return $this->json([
            'status' => 'success',
            'comment_item' => $this->renderView('partials/_user-comment-item.html.twig', [
                'userComment' => $userComment,
            ]),
            // Resend a fresh form, in case the previous contains errors
            'form' => $this->renderCommentForm($this->createForm(PostCommentForm::class), $entity),
        ]);
    }

    #[Route('/post-comment/{id}/delete', name: 'post_comment_delete', methods: ['POST'])]
    #[IsGranted(User::ROLE_USER)]
    public function postCommentDelete(Request $request, UserComment $postComment, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('user-interactions', $request->headers->get('X-CSRF-TOKEN')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $this->em->remove($postComment);
        $this->em->flush();

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

    private function renderCommentForm(FormInterface $form, Post|Wall $entity): string
    {
        return $this->renderView('partials/_user-comment-form.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }
}
