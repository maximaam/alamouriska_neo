<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class HttpUtils
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function redirectToReferer(string $fallbackRoute, int $status = Response::HTTP_SEE_OTHER): RedirectResponse
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return new RedirectResponse($this->urlGenerator->generate($fallbackRoute));
        }

        $referer = $request->headers->get('referer');

        if (null !== $referer && str_starts_with($referer, $request->getSchemeAndHttpHost())) {
            return new RedirectResponse($referer, $status);
        }

        return new RedirectResponse($this->urlGenerator->generate($fallbackRoute), $status);
    }
}
