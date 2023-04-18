<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\EventListener;

use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistryLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CommandListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => 'onUpdate',
        ];
    }

    public function __construct(private BotLocator $botLocator, private CommandRegistryLocator $registryLocator)
    {
    }

    public function onUpdate(UpdateEvent $event): void
    {
        $api = $this->botLocator->get($event->getBot());
        $registry = $this->registryLocator->get($event->getBot());

        foreach ($registry->getCommands() as $command) {
            if (!$command->isApplicable($event->getUpdate())) {
                continue;
            }

            $command->execute($api, $event->getUpdate());
            $event->setProcessed();

            break;
        }
    }
}
