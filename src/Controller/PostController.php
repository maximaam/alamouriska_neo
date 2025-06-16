<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostType;
use App\Form\PostForm;
use App\Repository\PostCommentRepository;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\TruncateMode;

use function Symfony\Component\String\u;

#[Route('/post', name: 'app_post_', priority: 1)]
#[IsGranted(User::ROLE_USER)]
final class PostController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = (new Post())->setUser($user);
        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (\in_array($post->getType(), [PostType::proverb, PostType::joke], true)) {
                $title = u($post->getDescription())->truncate(50, '…', cut: TruncateMode::WordBefore);
                $post->setTitle((string) $title);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_home_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user, Post $post, PostLikeRepository $postLikeRepo, PostCommentRepository $postCommentRepo): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'likedPostIds' => $postLikeRepo->findLikedPostIdsByUser($post, $user),
            'commentPostIds' => $postCommentRepo->findCommentPostIdsByUser($post, $user),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'flash.post_edit_success');

            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[CurrentUser] User $user, Request $request, Post $post, EntityManagerInterface $entityManager, PostLikeRepository $postLikeRepo, PostCommentRepository $postCommentRepo): Response
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($post);
                $entityManager->flush();

                $this->addFlash('success', 'flash.post_deleted_success');

                return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
            }
        }

        $this->addFlash('warning', 'flash.post_delete_warning');

        return $this->render('post/delete.html.twig', [
            'post' => $post,
            'likedPostIds' => $postLikeRepo->findLikedPostIdsByUser($post, $user),
            'commentPostIds' => $postCommentRepo->findCommentPostIdsByUser($post, $user),
        ]);
    }
}
