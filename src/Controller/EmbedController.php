<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserCommentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

// No route for embed controller
final class EmbedController extends AbstractController
{
    public function sidebar(PostRepository $postRepo, UserCommentRepository $commentRepo, UserRepository $userRepo): Response
    {
        return $this->render('embed/_sidebar.html.twig', [
            'newest_posts' => $postRepo->fetchNewestSidebar(5),
            'newest_users' => $userRepo->findNewest(),
            'newest_questions' => $postRepo->fetchQuestions(),
            'newest_comments' => $commentRepo->findNewest(),
        ]);
    }
}
