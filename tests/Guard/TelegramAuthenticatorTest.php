<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Guard;

use BoShurik\TelegramBotBundle\Exception\AuthenticationException;
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Guard\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TelegramAuthenticatorTest extends TestCase
{
    private const GUARD_ROUTE_NAME = 'guard';
    private const LOGIN_ROUTE_NAME = 'login';
    private const TARGET_ROUTE_NAME = 'target';

    private $auth;
    private $urlGenerator;
    private $userFactory;
    private $userLoader;
    private $validator;

    public function setUp(): void
    {
        $this->validator = $this->createMock(TelegramLoginValidator::class);
        $this->userFactory = $this->createMock(UserFactoryInterface::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $guardRoute = self::GUARD_ROUTE_NAME;
        $defaultTargetRoute = self::TARGET_ROUTE_NAME;
        $loginRoute = self::LOGIN_ROUTE_NAME;

        $this->auth = new TelegramAuthenticator($this->validator, $this->userLoader, $this->userFactory, $this->urlGenerator, $guardRoute, $defaultTargetRoute, $loginRoute);
    }

    public function testSupportGuardRoute(): void
    {
        $request = new Request([], [], ['_route' => 'whatever']);

        $this->assertFalse($this->auth->supports($request), sprintf('Should not support `%s` route', $request->attributes->get('_route')));

        $request = new Request([], [], ['_route' => self::GUARD_ROUTE_NAME]);

        $this->assertTrue($this->auth->supports($request), sprintf('Should support `%s` route', $request->attributes->get('_route')));
    }

    public function testCheckCredentialsReturnTrue(): void
    {
        $credentials = 'Can be any value';
        $user = $this->createMock(UserInterface::class);

        $this->assertTrue($this->auth->checkCredentials($credentials, $user), 'Should EVER return TRUE because real validation was done in another step');
    }

    public function testGetCredentials(): void
    {
        $credentials = ['id' => 0, 'username' => 'someone', 'auth' => 'a secret token'];
        $request = new Request($credentials);

        $this->assertSame($credentials, $this->auth->getCredentials($request));
    }

    public function testGetLoginUrl(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(self::LOGIN_ROUTE_NAME)
            ->willReturn('/login');

        $request = new Request();

        $response = $this->auth->start($request);
        $this->assertSame(302, $response->getStatusCode(), 'Should be 302 redirect');
        $this->assertSame('/login', $response->getTargetUrl(), 'User should be redirected to the login page');
    }

    public function testAuthenticationFailure(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(self::LOGIN_ROUTE_NAME)
            ->willReturn('/login');

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $authenticationException = $this->createMock(AuthenticationException::class);

        $response = $this->auth->onAuthenticationFailure($request, $authenticationException);
        $this->assertSame(302, $response->getStatusCode(), 'Should be 302 redirect');
        $this->assertSame('/login', $response->getTargetUrl(), 'User should be redirected to the login page');
        $this->assertSame($authenticationException, $request->getSession()->get('_security.last_error'), 'Session should contain the last authentication exception');
    }

    public function testAuthenticationSuccess(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(self::TARGET_ROUTE_NAME)
            ->willReturn('/reserved');

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $token = $this->createMock(TokenInterface::class);
        $firewallName = 'main';

        $response = $this->auth->onAuthenticationSuccess($request, $token, $firewallName);
        $this->assertSame(302, $response->getStatusCode(), 'Should be 302 redirect');
        $this->assertSame('/reserved', $response->getTargetUrl(), 'User should be redirected to the reserved area page');
    }

    public function testCredentialsAreValidated(): void
    {
        $credentials = ['Invalid credentials'];

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($credentials)
            ->willThrowException($fail = new AuthenticationException());

        $uselessSymfonyUserProvider = $this->createMock(UserProviderInterface::class);

        $this->expectExceptionObject($fail);

        $this->auth->getUser($credentials, $uselessSymfonyUserProvider);
    }

    public function testAuthenticatedUserWasLoaded(): void
    {
        $credentials = ['id' => 0];

        $this->userLoader
            ->expects($this->once())
            ->method('loadByTelegramId')
            ->with(0)
            ->willReturn($dummyUser = $this->createMock(UserInterface::class));

        $uselessSymfonyUserProvider = $this->createMock(UserProviderInterface::class);

        $authenticatedUser = $this->auth->getUser($credentials, $uselessSymfonyUserProvider);
        $this->assertSame($dummyUser, $authenticatedUser);
    }

    public function testNewUserWasCreatedIfNotFoundByLoader(): void
    {
        $credentials = ['id' => 0];

        $this->userLoader
            ->expects($this->once())
            ->method('loadByTelegramId')
            ->with(0)
            ->willReturn(null);

        $uselessSymfonyUserProvider = $this->createMock(UserProviderInterface::class);

        $authenticatedUser = $this->auth->getUser($credentials, $uselessSymfonyUserProvider);
        $this->assertInstanceOf(UserInterface::class, $authenticatedUser, 'A valid UserInterface instance should be returned');
    }
}
