<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use App\Entity\Post;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/embed', name: 'app_embed_', priority: 5)]
final class EmbedController extends AbstractController
{
    #[Route('/sidebar', name: 'sidebar', methods: ['GET'], requirements: ['typeSlug' => Requirement::ASCII_SLUG])]
    public function sidebar(PostRepository $postRepo): Response
    {
        return $this->render('embed/_sidebar.html.twig', [
            'latest_posts' => $postRepo->findAll(),
        ]);
    }
}
