<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait RedirectIfAuthenticatedTrait
{
    protected function redirectIfAuthenticated(Security $security, string $routeName = 'app_home_index'): ?RedirectResponse
    {
        if ($security->getUser()) {
            return new RedirectResponse($this->generateUrl($routeName));
        }

        return null;
    }
}
