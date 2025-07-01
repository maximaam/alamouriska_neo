<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final readonly class OAuthRegistration
{
    public function register(ResourceOwnerInterface $resourceOwner, EntityManagerInterface $em): User
    {
        $user = match (true) {
            $resourceOwner instanceof GoogleUser => $user = (new User())
               ->setEmail((string) $resourceOwner->getEmail())
               ->setPassword(md5((string) $resourceOwner->getEmail())) // Just a placeholder
               ->setPseudo((string) strstr((string) $resourceOwner->getEmail(), '@', true))
               ->setIsVerified(true)
               ->setGoogleId($resourceOwner->getId()),

            /*
             $resourceOwner instanceof Facebook => $user = (new User())
                ->setEmail($resourceOwner->getEmail())
                ->setPassword(md5($resourceOwner->getEmail()))
                ->setPseudo($resourceOwner->getEmail())
                ->setIsVerified(true)
                ->setGoogleId($resourceOwner->getId()),
            */

            default => throw new \LogicException('Unsupported OAuth provider'),
        };

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
