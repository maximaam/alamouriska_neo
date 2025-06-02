<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

// No route for embed controller
final class EmbedController extends AbstractController
{
    public function sidebar(PostRepository $postRepo): Response
    {
        return $this->render('embed/_sidebar.html.twig', [
            'latest_posts' => $postRepo->findLatests(),
        ]);
    }
}
