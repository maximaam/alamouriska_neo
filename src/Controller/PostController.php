<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/post', name: 'app_post_', priority: 2)]
final class PostController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] User $user, Request $request): Response
    {
        $post = (new Post())->setUser($user);
        $form = $this->createForm(PostForm::class, $post)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('app_frontend_index', status: Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostForm::class, $post)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'flash.post_edit_success');

            return $this->redirectToRoute('app_frontend_member_profile', ['pseudo' => $post->getUser()->getPseudo()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[CurrentUser] User $user, Request $request, Post $post): Response
    {
        if ($request->isMethod(Request::METHOD_POST) && $this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($post);
            $this->em->flush();
            $this->addFlash('success', 'flash.post_deleted_success');

            return $this->redirectToRoute('app_frontend_member_profile', ['pseudo' => $post->getUser()->getPseudo()], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('warning', 'flash.post_delete_warning');

        return $this->render('post/delete.html.twig', [
            'post' => $post,
        ]);
    }
}
