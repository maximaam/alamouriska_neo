<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile', name: 'app_profile_', priority: 3)]
final class ProfileController extends AbstractController
{
    #[Route('/{pseudo}', name: 'index')]
    public function index(UserRepository $userRepository, string $pseudo): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $userRepository->findOneBy(['pseudo' => $pseudo]),
        ]);
    }
}
