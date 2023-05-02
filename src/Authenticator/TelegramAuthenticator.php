<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Authenticator;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class TelegramAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private TelegramLoginValidator $validator,
        private UserLoaderInterface $userLoader,
        private ?UserFactoryInterface $userFactory,
        private UrlGeneratorInterface $urlGenerator,
        private string $guardRoute,
        private string $defaultTargetRoute,
        private ?string $loginRoute = null
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $route = $request->attributes->get('_route');

        return $route === $this->guardRoute;
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $request->query->all();

        $this->validator->validate($credentials);
        $user = $this->userLoader->loadByTelegramId($credentials['id']);

        if (!$user && $this->userFactory) {
            $user = $this->userFactory->createFromTelegram($credentials);
        }

        if (!$user) {
            throw new BadCredentialsException();
        }

        return new SelfValidatingPassport(new UserBadge($credentials['id'], function ($id) {
            return $this->userLoader->loadByTelegramId($id);
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate($this->defaultTargetRoute));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($this->loginRoute) {
            return new RedirectResponse($this->urlGenerator->generate($this->loginRoute));
        }

        return null;
    }
}
