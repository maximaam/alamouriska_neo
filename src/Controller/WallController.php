<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Wall;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Form\WallForm;
use App\Utils\SocialMediaUtils;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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

            $description = SocialMediaUtils::linkifyUrls($description);
            $description = SocialMediaUtils::makeYoutubeEmbed($description);

            $wall->setDescription($description);

            $entityManager->persist($wall);
            $entityManager->flush();

            return $this->redirectToRoute('app_frontend_wall', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wall/new.html.twig', [
            'wall' => $wall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wall_show', methods: ['GET'])]
    public function show(Wall $wall): Response
    {
        return $this->render('wall/show.html.twig', [
            'wall' => $wall,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wall $wall, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WallForm::class, $wall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_frontend_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wall/edit.html.twig', [
            'wall' => $wall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Wall $wall, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$wall->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wall);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_frontend_index', [], Response::HTTP_SEE_OTHER);
    }
}
