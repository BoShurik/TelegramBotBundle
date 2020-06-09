<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\DependencyInjection;

use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use TelegramBot\Api\BotApi;

class BoShurikTelegramBotExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new BoShurikTelegramBotExtension(),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        return [
            'api' => [
                'token' => 'secret',
            ],
        ];
    }

    public function testContainerBuildsWithMinimalConfiguration(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(BotApi::class);
    }

    public function testAuthenticatorIsConfigured(): void
    {
        $this->load([
            'guard' => [
                'guard_route' => 'guard_route',
                'default_target_route' => 'reaserved_area',
            ],
        ]);

        $this->assertContainerBuilderHasService(TelegramLoginValidator::class);
        $this->assertContainerBuilderHasService(TelegramAuthenticator::class);
    }
}
