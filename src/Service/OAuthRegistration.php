<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final readonly class OAuthRegistration
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function register(ResourceOwnerInterface $resourceOwner, EntityManagerInterface $em): User
    {
        $email = (string) $resourceOwner->getEmail();
        if ($user = $this->userRepository->findOneBy(['email' => $email])) {
            // Optionally associate the Google ID if not already linked
            if ($resourceOwner instanceof GoogleUser && !$user->getGoogleId()) {
                $user->setGoogleId($resourceOwner->getId());
                $em->flush();
            }

            return $user;
        }

        $user = match (true) {
            $resourceOwner instanceof GoogleUser => new User()
               ->setEmail($email)
               ->setPassword(uniqid()) // Just a placeholder
               ->setPseudo((string) strstr($email, '@', true))
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
