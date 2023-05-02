<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BoShurik\TelegramBotBundle\Command\UpdatesCommand;
use BoShurik\TelegramBotBundle\Command\Webhook\InfoCommand;
use BoShurik\TelegramBotBundle\Command\Webhook\SetCommand;
use BoShurik\TelegramBotBundle\Command\Webhook\UnsetCommand;
use BoShurik\TelegramBotBundle\Controller\WebhookController;
use BoShurik\TelegramBotBundle\EventListener\CommandListener;
use BoShurik\TelegramBotBundle\Messenger\MessageHandler;
use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistryLocator;
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TelegramBot\Api\BotApi;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('boshurik_telegram_bot.api.abstract_bot', BotApi::class)
        ->abstract()
        ->call('setProxy', ['%boshurik_telegram_bot.api.proxy%']);

    $services->set('boshurik_telegram_bot.api.bot_locator', ServiceLocator::class)
        ->args([[]])
        ->tag('container.service_locator')
    ;

    $services->set(BotLocator::class)
        ->args([
            service('boshurik_telegram_bot.api.bot_locator'),
        ])
    ;

    $services->set('boshurik_telegram_bot.telegram', Telegram::class)
        ->args([
            service(BotLocator::class),
            service('event_dispatcher'),
        ]);

    $services
        ->set('boshurik_telegram_bot.command.abstract_registry', CommandRegistry::class)
        ->abstract()
    ;

    $services->set('boshurik_telegram_bot.command.registry_locator', ServiceLocator::class)
        ->args([[]])
        ->tag('container.service_locator')
    ;

    $services->set('boshurik_telegram_bot.command.registries', CommandRegistryLocator::class)
        ->args([
            service('boshurik_telegram_bot.command.registry_locator'),
        ])
    ;

    $services->set('boshurik_telegram_bot.command.listener', CommandListener::class)
        ->args([
            service(BotLocator::class),
            service('boshurik_telegram_bot.command.registries'),
        ])
        ->tag('kernel.event_subscriber');

    $services->set(WebhookController::class)
        ->public()
        ->args([
            service('boshurik_telegram_bot.telegram'),
            service('event_dispatcher'),
            service('messenger.default_bus')->nullOnInvalid(),
        ]);

    $services->set('boshurik_telegram_bot.command.updates', UpdatesCommand::class)
        ->args([
            service('boshurik_telegram_bot.telegram'),
        ])
        ->tag('console.command');

    $services->set('boshurik_telegram_bot.command.webhook.set', SetCommand::class)
        ->args([
            service(BotLocator::class),
            service('router'),
        ])
        ->tag('console.command');

    $services->set('boshurik_telegram_bot.command.webhook.unset', UnsetCommand::class)
        ->args([
            service(BotLocator::class),
        ])
        ->tag('console.command');

    $services->set('boshurik_telegram_bot.command.webhook.info', InfoCommand::class)
        ->args([
            service(BotLocator::class),
        ])
        ->tag('console.command');

    $services->set('boshurik_telegram_bot.messenger.handler', MessageHandler::class)
        ->args([
            service('boshurik_telegram_bot.telegram'),
        ])
        ->tag('messenger.message_handler');
};
