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
use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\BotApi;

class BoShurikTelegramBotExtensionTest extends TestCase
{
    public function testContainer(): void
    {
        $extension = new BoShurikTelegramBotExtension();
        $container = $this->createContainer();

        $extension->load([
            'boshurik_telegram_bot' => [
                'api' => [
                    'token' => 'secret',
                ],
                'authenticator' => [
                    'guard_route' => 'guard_route',
                    'default_target_route' => 'secure_area',
                ],
            ],
        ], $container);

        $this->assertTrue($container->hasAlias(BotApi::class));
        $this->assertTrue($container->hasAlias(BotApi::class.' $default'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $defaultBot'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $defaultBotApi'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $defaultApi'));

        $this->assertTrue($container->has('boshurik_telegram_bot.api.bot.default'));

        $this->assertTrue($container->has(BotLocator::class));
        $this->assertTrue($container->has('boshurik_telegram_bot.command.registries'));

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

        $container->setAlias('bot_locator', new Alias('boshurik_telegram_bot.api.bot_locator', true));
        $container->setAlias('registry_locator', new Alias('boshurik_telegram_bot.command.registry_locator', true));

        $container->compile();

        /** @var ServiceLocator $botLocator */
        $botLocator = $container->get('bot_locator');
        /** @var ServiceLocator $registryLocator */
        $registryLocator = $container->get('registry_locator');

        $this->assertEquals([
            'default' => '?',
        ], $botLocator->getProvidedServices());
        $this->assertEquals([
            'default' => '?',
        ], $registryLocator->getProvidedServices());

        $this->assertInstanceOf(BotApi::class, $botLocator->get('default'));

        $this->assertInstanceOf(CommandRegistry::class, $registryLocator->get('default'));
    }

    public function testContainerWithMultipleBots(): void
    {
        $extension = new BoShurikTelegramBotExtension();
        $container = $this->createContainer();

        $extension->load([
            'boshurik_telegram_bot' => [
                'api' => [
                    'default_bot' => 'first',
                    'bots' => [
                        'first' => 'secret',
                        'second' => 'secret',
                    ],
                ],
                'authenticator' => [
                    'guard_route' => 'guard_route',
                    'default_target_route' => 'secure_area',
                ],
            ],
        ], $container);

        $this->assertTrue($container->hasAlias(BotApi::class));
        $this->assertTrue($container->hasAlias(BotApi::class.' $first'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $firstBot'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $firstBotApi'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $firstApi'));

        $this->assertTrue($container->hasAlias(BotApi::class.' $second'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $secondBot'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $secondBotApi'));
        $this->assertTrue($container->hasAlias(BotApi::class.' $secondApi'));

        $this->assertTrue($container->has('boshurik_telegram_bot.api.bot.first'));
        $this->assertTrue($container->has('boshurik_telegram_bot.api.bot.second'));

        $this->assertTrue($container->has(BotLocator::class));
        $this->assertTrue($container->has('boshurik_telegram_bot.command.registries'));

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

        $container->setAlias('bot_locator', new Alias('boshurik_telegram_bot.api.bot_locator', true));
        $container->setAlias('registry_locator', new Alias('boshurik_telegram_bot.command.registry_locator', true));

        $container->compile();

        /** @var ServiceLocator $botLocator */
        $botLocator = $container->get('bot_locator');
        /** @var ServiceLocator $registryLocator */
        $registryLocator = $container->get('registry_locator');

        $this->assertEquals([
            'first' => '?',
            'second' => '?',
        ], $botLocator->getProvidedServices());
        $this->assertEquals([
            'first' => '?',
            'second' => '?',
        ], $registryLocator->getProvidedServices());

        $this->assertInstanceOf(BotApi::class, $botLocator->get('first'));
        $this->assertInstanceOf(BotApi::class, $botLocator->get('second'));

        $this->assertInstanceOf(CommandRegistry::class, $registryLocator->get('first'));
        $this->assertInstanceOf(CommandRegistry::class, $registryLocator->get('second'));
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->set('event_dispatcher', $this->createMock(EventDispatcherInterface::class));

        return $container;
    }
}
