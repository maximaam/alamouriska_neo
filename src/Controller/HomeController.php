<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_home_')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PostRepository $postRepo): Response
    {
        return $this->render('home/index.html.twig', [
            'latest_posts' => $postRepo->findAll(),
        ]);
    }
}
