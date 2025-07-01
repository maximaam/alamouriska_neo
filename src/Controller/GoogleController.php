<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
    ];

    #[Route('/connect/{service}', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry, string $service): RedirectResponse
    {
        if (!\in_array($service, array_keys(self::SCOPES), true)) {
            throw $this->createNotFoundException();
        }

        return $clientRegistry
            ->getClient($service)
            ->redirect(self::SCOPES[$service], []);
    }

    #[Route('/connect/{service}/check', name: 'connect_google_check', requirements: ['service' => 'google|facebook'])]
    public function check(): Response
    {
        return new Response(status: Response::HTTP_OK);
    }
}
