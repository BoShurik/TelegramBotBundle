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
     * @var null|UserFactoryInterface
     */
    private $userFactory;

    /**
     * @var UserLoaderInterface
     */
    private $userLoader;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $guardRoute;

    /**
     * @var string|null
     */
    private $loginRoute;

    /**
     * @var string
     */
    private $defaultTargetRoute;

    public function __construct(TelegramLoginValidator $validator, ?UserFactoryInterface $userFactory, UserLoaderInterface $userLoader, UrlGeneratorInterface $urlGenerator, string $guardRoute, string $defaultTargetRoute, string $loginRoute = null)
    {
        $this->validator = $validator;
        $this->userFactory = $userFactory;
        $this->userLoader = $userLoader;
        $this->urlGenerator = $urlGenerator;
        $this->guardRoute = $guardRoute;
        $this->loginRoute = $loginRoute;
        $this->defaultTargetRoute = $defaultTargetRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        $route = $request->attributes->get('_route');

        return $route === $this->guardRoute;
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

        $user = $this->userLoader->loadByTelegramId($credentials['id']);

        if (!$user && $this->userFactory) {
            return $this->userFactory->createFromTelegram($credentials);
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

        return new RedirectResponse($this->urlGenerator->generate($this->defaultTargetRoute));
    }

    protected function getLoginUrl(): string
    {
        if (!$this->loginRoute) {
            throw new \LogicException('`login_route` parameter si required if you don\'t use other authentication mechanisms in order to redirect users to a login page');
        }

        return $this->urlGenerator->generate($this->loginRoute);
    }
}
