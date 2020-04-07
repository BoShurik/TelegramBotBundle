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

use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;

class BoShurikTelegramBotExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new BoShurikTelegramBotExtension()
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'api' => [
                'token' => 'secret',
            ]
        ];
    }

    public function testContainerBuildsWithMinimalConfiguration()
    {
        $this->load();

        $this->assertContainerBuilderHasService(\TelegramBot\Api\BotApi::class);
    }

    public function testAuthenticatorIsConfigured()
    {
        $this->load([
            'guard' => [
                'default_target_route' => 'reaserved_area',
            ]
        ]);

        $this->assertContainerBuilderHasService(\BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator::class);
        $this->assertContainerBuilderHasService(\BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator::class);
    }
}