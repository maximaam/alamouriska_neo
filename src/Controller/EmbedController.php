<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\PostCommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

// No route for embed controller
final class EmbedController extends AbstractController
{
    public function sidebar(RequestStack $requestStack, PageRepository $pageRepo, PostCommentRepository $commentRepo): Response
    {
        $mainRequest = $requestStack->getMainRequest();

        return $this->render('embed/_sidebar.html.twig', [
            'page' => $pageRepo->findOneBy(['alias' => 'home']),
            'newset_comments' => $commentRepo->findLatests(),
            'route_params' => $mainRequest?->attributes->get('_route_params'),
        ]);
    }
}
