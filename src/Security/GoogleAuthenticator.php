<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $oauthServiceName = 'google';

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $userRepository): ?User
    {
        if (!$resourceOwner instanceof GoogleUser) {
            throw new \RuntimeException('expecting google user');
        }

        if (true !== ($resourceOwner->toArray()['email_verified'] ?? null)) {
            throw new AuthenticationException('email not verified');
        }

        return $userRepository
            ->findOneBy([
                'googleId' => $resourceOwner->getId(),
                'email' => $resourceOwner->getEmail(),
            ]);
    }
}
