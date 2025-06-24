<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\PostCommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

// No route for embed controller
final class EmbedController extends AbstractController
{
    public function sidebar(RequestStack $requestStack, PostRepository $postRepo, PostCommentRepository $commentRepo, UserRepository $userRepo): Response
    {
        $mainRequest = $requestStack->getMainRequest();

        return $this->render('embed/_sidebar.html.twig', [
            'newest_users' => $userRepo->findNewest(),
            'newset_questions' => $postRepo->findQuestions(),
            'newset_comments' => $commentRepo->findNewest(),
            'route_params' => $mainRequest?->attributes->get('_route_params'),
        ]);
    }
}
