<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class NoSessionLoginEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $url = $this->urlGenerator->generate('app_security_login');
        $response = new RedirectResponse($url);
        // Make sure it's cacheable and doesn't send Set-Cookie
        $response->headers->remove('Set-Cookie');
        $response->setPublic();
        $response->setSharedMaxAge(600);

        return $response;
    }
}
