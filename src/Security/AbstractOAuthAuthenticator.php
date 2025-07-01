<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\OAuthRegistration;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class AbstractOAuthAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    protected string $oauthServiceName = '';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly OAuthRegistration $oAuthRegistration,
    ) {
    }

    public function supports(Request $request): bool
    {
        return 'connect_google_check' === $request->attributes->get('_route')
            && $request->get('service') === $this->oauthServiceName;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if (null !== $targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_frontend_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $authenticationException): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $authenticationException);
        }

        return new RedirectResponse($this->router->generate('app_security_login'));
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->fetchAccessToken($this->getClient());
        $resourceOwner = $this->getResourceOwnerFromCredentials($credentials);

        if (null === $user = $this->getUserFromResourceOwner($resourceOwner, $this->userRepository)) {
            $user = $this->oAuthRegistration->register($resourceOwner, $this->em);
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), fn () => $user),
            badges: [
                new RememberMeBadge(),
            ]
        );
    }

    protected function getResourceOwnerFromCredentials(AccessToken $credentials): ResourceOwnerInterface
    {
        return $this->getClient()->fetchUserFromToken($credentials);
    }

    private function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient($this->oauthServiceName);
    }

    abstract protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $userRepository): ?User;
}
