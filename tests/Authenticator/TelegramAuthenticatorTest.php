<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Authenticator;

use BoShurik\TelegramBotBundle\Authenticator\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Authenticator\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Authenticator\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Authenticator\UserLoaderInterface;
use BoShurik\TelegramBotBundle\Exception\AuthenticationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class TelegramAuthenticatorTest extends TestCase
{
    private const GUARD_ROUTE_NAME = 'guard';
    private const LOGIN_ROUTE_NAME = 'login';
    private const TARGET_ROUTE_NAME = 'target';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var UserFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userFactory;

    /**
     * @var UserLoaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userLoader;

    /**
     * @var TelegramLoginValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(TelegramLoginValidator::class);
        $this->userFactory = $this->createMock(UserFactoryInterface::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testSupports(): void
    {
        $authenticator = $this->createAuthenticator();

        $request = new Request([], [], ['_route' => 'whatever']);
        $this->assertFalse($authenticator->supports($request));

        $request = new Request([], [], ['_route' => self::GUARD_ROUTE_NAME]);
        $this->assertTrue($authenticator->supports($request));
    }

    public function testOnAuthenticationSuccessWithTargetPath(): void
    {
        $this->urlGenerator
            ->expects($this->never())
            ->method('generate')
        ;

        $request = new Request();
        $request->setSession($session = new Session(new MockArraySessionStorage()));
        $session->set('_security.main.target_path', '/session');

        $token = $this->createMock(TokenInterface::class);
        $firewallName = 'main';

        $authenticator = $this->createAuthenticator();
        $response = $authenticator->onAuthenticationSuccess($request, $token, $firewallName);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/session', $response->getTargetUrl());
    }

    public function testOnAuthenticationSuccessWithDefaultPath(): void
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

        $authenticator = $this->createAuthenticator();
        $response = $authenticator->onAuthenticationSuccess($request, $token, $firewallName);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/reserved', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailure(): void
    {
        $authenticator = $this->createAuthenticatorWithoutLoginRoute();

        $request = new Request();

        $response = $authenticator->onAuthenticationFailure($request, new AuthenticationException('Oops'));

        $this->assertNull($response);
    }

    public function testOnAuthenticationFailureWithLoginRoute(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(self::LOGIN_ROUTE_NAME)
            ->willReturn('/login');

        $authenticator = $this->createAuthenticator();

        $request = new Request();

        $response = $authenticator->onAuthenticationFailure($request, new AuthenticationException('Oops'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testAuthenticate(): void
    {
        $this->userLoader
            ->expects($this->exactly(2))
            ->method('loadByTelegramId')
            ->with(42)
            ->willReturn($user = $this->createMock(UserInterface::class));

        $authenticator = $this->createAuthenticatorWithoutUserFactory();

        $request = new Request([
            'id' => 42,
        ]);

        $passport = $authenticator->authenticate($request);

        $this->assertInstanceOf(Passport::class, $passport);
        $this->assertInstanceOf(UserInterface::class, $passport->getUser());
        $this->assertSame($user, $passport->getUser());
    }

    public function testAuthenticateWithUserFactory(): void
    {
        $this->userLoader
            ->expects($this->exactly(2))
            ->method('loadByTelegramId')
            ->with(42)
            ->willReturnOnConsecutiveCalls(null, $user = $this->createMock(UserInterface::class));

        $this->userFactory
            ->expects($this->once())
            ->method('createFromTelegram')
            ->with([
                'id' => 42,
            ])
            ->willReturn($user)
        ;

        $authenticator = $this->createAuthenticator();

        $request = new Request([
            'id' => 42,
        ]);

        $passport = $authenticator->authenticate($request);

        $this->assertInstanceOf(Passport::class, $passport);
        $this->assertInstanceOf(UserInterface::class, $passport->getUser());
        $this->assertSame($user, $passport->getUser());
    }

    public function testAuthenticateException(): void
    {
        $this->userLoader
            ->expects($this->once())
            ->method('loadByTelegramId')
            ->with(42)
            ->willReturn(null);

        $authenticator = $this->createAuthenticatorWithoutUserFactory();

        $request = new Request([
            'id' => 42,
        ]);

        $this->expectException(BadCredentialsException::class);
        $authenticator->authenticate($request);
    }

    private function createAuthenticator(): TelegramAuthenticator
    {
        return new TelegramAuthenticator(
            $this->validator,
            $this->userLoader,
            $this->userFactory,
            $this->urlGenerator,
            self::GUARD_ROUTE_NAME,
            self::TARGET_ROUTE_NAME,
            self::LOGIN_ROUTE_NAME
        );
    }

    private function createAuthenticatorWithoutUserFactory(): TelegramAuthenticator
    {
        return new TelegramAuthenticator(
            $this->validator,
            $this->userLoader,
            null,
            $this->urlGenerator,
            self::GUARD_ROUTE_NAME,
            self::TARGET_ROUTE_NAME,
            self::LOGIN_ROUTE_NAME
        );
    }

    private function createAuthenticatorWithoutLoginRoute(): TelegramAuthenticator
    {
        return new TelegramAuthenticator(
            $this->validator,
            $this->userLoader,
            $this->userFactory,
            $this->urlGenerator,
            self::GUARD_ROUTE_NAME,
            self::TARGET_ROUTE_NAME
        );
    }
}
