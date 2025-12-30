<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/connect', name: 'connect_google_')]
class GoogleController extends AbstractController
{
    public const array SCOPES = [
        'google' => [],
    ];

    #[Route('/{service}', name: 'start')]
    public function connect(ClientRegistry $clientRegistry, string $service): RedirectResponse
    {
        if (!\in_array($service, array_keys(self::SCOPES), true)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry
            ->getClient($service)
            ->redirect(self::SCOPES[$service], []);
    }

    #[Route('/{service}/check', name: 'check', requirements: ['service' => 'google|facebook'])]
    public function check(): Response
    {
        return new Response(status: Response::HTTP_OK);
    }
}
