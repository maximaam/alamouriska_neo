<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wall;
use App\Form\WallForm;
use App\Utils\SocialMediaUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wall', name: 'app_wall_')]
#[IsGranted(User::ROLE_USER)]
final class WallController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $wall = new Wall();
        $wall->setUser($user);

        $form = $this->createForm(WallForm::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $form->get('description')->getData();

            $descriptionHtml = SocialMediaUtils::linkifyUrls($description, true);
            $descriptionHtml = SocialMediaUtils::makeYoutubeEmbed($descriptionHtml);

            $wall->setDescriptionHtml($descriptionHtml);

            $entityManager->persist($wall);
            $entityManager->flush();

            return $this->redirectToRoute('app_frontend_walls', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wall/new.html.twig', [
            'wall' => $wall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wall $wall, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WallForm::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $form->get('description')->getData();

            $descriptionHtml = SocialMediaUtils::linkifyUrls($description);
            $descriptionHtml = SocialMediaUtils::makeYoutubeEmbed($descriptionHtml);

            $wall->setDescriptionHtml($descriptionHtml);

            $entityManager->persist($wall);
            $entityManager->flush();

            return $this->redirectToRoute('app_frontend_wall', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wall/edit.html.twig', [
            'wall' => $wall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(#[CurrentUser] User $user, Request $request, Wall $wall): Response
    {
        if ($request->isMethod(Request::METHOD_POST) && $this->isCsrfTokenValid('delete'.$wall->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($wall);
            $this->em->flush();
            $this->addFlash('success', 'flash.wall_deleted_success');

            return $this->redirectToRoute('app_user_show', status: Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('warning', 'flash.wall_delete_warning');

        return $this->render('wall/delete.html.twig', [
            'wall' => $wall,
            // 'liked_post_ids' => $this->em->getRepository(PostLike::class)->findLikedPostIdsByUser($post, $user),
            // 'comment_post_ids' => $this->em->getRepository(PostComment::class)->findCommentPostIdsByUser($post, $user),
        ]);
    }
}
