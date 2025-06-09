<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/user', name: 'app_user_', priority: 2)]
final class UserController extends AbstractController
{
    #[Route('/show', name: 'show', methods: ['GET'])]
    public function show(#[CurrentUser] User $user, PostRepository $postRepository): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'posts' => $postRepository->findBy(['user' => $user]),
        ]);
    }

    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'flash.profile_edit_success');

            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($user);
                $entityManager->flush();

                // Clear the runnig session
                $tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                $this->addFlash('success', 'flash.user_deleted_success');

                return $this->redirectToRoute('app_home_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $this->addFlash('warning', 'flash.user_delete_warning');

        return $this->render('user/delete.html.twig', [
            'user' => $user,
        ]);
    }
}
