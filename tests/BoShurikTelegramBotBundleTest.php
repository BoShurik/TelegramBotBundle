<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests;

use BoShurik\TelegramBotBundle\BoShurikTelegramBotBundle;
use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\CommandCompilerPass;
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BoShurikTelegramBotBundleTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return array(
            new BoShurikTelegramBotExtension()
        );
    }

    public function testGetContainerExtension()
    {
        $bundle = new BoShurikTelegramBotBundle();

        $this->assertInstanceOf(BoShurikTelegramBotExtension::class, $bundle->getContainerExtension());
        $this->assertSame('boshurik_telegram_bot', $bundle->getContainerExtension()->getAlias());
    }

    public function testAddCompilerPass()
    {
        $bundle = new BoShurikTelegramBotBundle();

        /** @var ContainerBuilder|MockObject $builder */
        $builder = $this->createMock(ContainerBuilder::class);
        $builder
            ->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->callback(function($pass) {
                return $pass instanceof CommandCompilerPass;
            }));
        ;

        $bundle->build($builder);
    }

    public function testNoAuthenticatorServiceIfGuardIsDisabled()
    {
        $this->load([
            // Minimal config
            'api' => [
                'token' => 'my secret token',
            ]
        ]);

        $this->assertContainerBuilderNotHasService(TelegramAuthenticator::class);
    }

    public function testAuthenticatorServiceIsDefined()
    {
        $this->load([
            // Minimal config
            'api' => [
                'token' => 'my secret token',
            ],
            'guard' => [
                'default_target_route' => 'default_target_route',
                'guard_route' => 'guard_route'
            ]
        ]);

        $this->assertContainerBuilderHasService(TelegramAuthenticator::class);
    }
}