<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\DependencyInjection\Compiler;

use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\GuardCompilerPass;
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
use BoShurik\TelegramBotBundle\Tests\Fixtures\UserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class GuardCompilerPassTest extends TestCase
{
    public function testRegisterCommand()
    {
        $container = $container = $this->buildContainer();

        $container->compile();

        $authenticator = $container->get(TelegramAuthenticator::class);
    }

    private function getExtension(): BoShurikTelegramBotExtension
    {
        $extension = $this->createMock(BoShurikTelegramBotExtension::class);

        $extension->expects($this->once())
            ->method('getConfig')
            ->willReturn([
                'api' => [
                    'token' => 'my secret bot token'
                ],
                'guard' => [
                    'enabled' => true,
                    'guard_route' => 'guard_route',
                    'default_target_route' => 'default_target_route',
                ],
            ]);

        return $extension;
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new GuardCompilerPass($this->getExtension()));

        $container
            ->register(UserLoaderInterface::class, UserProvider::class)
        ;

        $container
            ->register(UrlGeneratorInterface::class, Router::class)
            ->addArgument($this->createMock(LoaderInterface::class))
            ->addArgument(null)
        ;

        return $container;
    }
}