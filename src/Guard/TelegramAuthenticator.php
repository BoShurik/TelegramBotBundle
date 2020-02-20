<?php

namespace BoShurik\TelegramBotBundle\Guard;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TelegramAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var TelegramLoginValidator
     */
    private $validator;

    /**
     * @var UserLoaderInterface|UserFactoryInterface
     */
    private $userProvider;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(TelegramLoginValidator $validator, UserLoaderInterface $userProvider, UrlGeneratorInterface $urlGenerator)
    {
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        $route = $request->attributes->get('_route');

        return $route === '_telegram_login';
    }

    /**
     * {@inheritdoc}
     */
    final public function getCredentials(Request $request)
    {
        return $request->query->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $this->validator->validate($credentials);

        $user = $this->userProvider->loadByTelegramId($credentials['id']);

        if (!$user && $this->userProvider instanceof UserFactoryInterface) {
            return $this->userProvider->createFromTelegram($credentials);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // The check was already done by `validate` method
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('user_profile'));
    }

    protected function getLoginUrl(): string
    {
        throw new \LogicException('Override this function if you don\'t have other authentication mechanisms in order to redirect users');
    }
}
