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

use BoShurik\TelegramBotBundle\Authenticator\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Authenticator\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Controller\WebhookController;
use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TelegramBot\Api\BotApi;

class BoShurikTelegramBotExtensionTest extends TestCase
{
    public function testContainer()
    {
        $extension = new BoShurikTelegramBotExtension();
        $container = new ContainerBuilder();

        $extension->load([
            'boshurik_telegram_bot' => [
                'api' => [
                    'token' => 'secret',
                ],
                'authenticator' => [
                    'guard_route' => 'guard_route',
                    'default_target_route' => 'reaserved_area',
                ],
            ],
        ], $container);

        $this->assertTrue($container->has(BotApi::class));
        $this->assertTrue($container->has(TelegramLoginValidator::class));
        $this->assertTrue($container->has(TelegramAuthenticator::class));
        $this->assertTrue($container->has(WebhookController::class));
        $this->assertTrue($container->has('boshurik_telegram_bot.telegram'));

        $commands = $container->findTaggedServiceIds('console.command');
        $this->assertArrayHasKey('boshurik_telegram_bot.command.updates', $commands);
        $this->assertArrayHasKey('boshurik_telegram_bot.command.webhook.set', $commands);
        $this->assertArrayHasKey('boshurik_telegram_bot.command.webhook.unset', $commands);
        $this->assertArrayHasKey('boshurik_telegram_bot.command.webhook.info', $commands);

        $messageHandlers = $container->findTaggedServiceIds('messenger.message_handler');
        $this->assertArrayHasKey('boshurik_telegram_bot.messenger.handler', $messageHandlers);

        $eventSubscribers = $container->findTaggedServiceIds('kernel.event_subscriber');
        $this->assertArrayHasKey('boshurik_telegram_bot.command.listener', $eventSubscribers);
    }
}
