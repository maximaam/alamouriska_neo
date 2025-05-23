<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/member', name: 'app_member_')]
final class MemberController extends AbstractController
{
    #[Route('/{displayName}', name: 'index')]
    public function index(UserRepository $userRepository, string $displayName): Response
    {
        return $this->render('member/index.html.twig', [
            'member' => $userRepository->findOneBy(['displayName' => $displayName]),
        ]);
    }
}
