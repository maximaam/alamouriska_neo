<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NoSessionLoginEntryPoint implements AuthenticationEntryPointInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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
