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
use BoShurik\TelegramBotBundle\Telegram\Command\CommandRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;

final class CommandListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => 'onUpdate',
        ];
    }

    public function __construct(private BotApi $api, private CommandRegistry $commandRegistry)
    {
    }

    public function onUpdate(UpdateEvent $event): void
    {
        foreach ($this->commandRegistry->getCommands() as $command) {
            if (!$command->isApplicable($event->getUpdate())) {
                continue;
            }

            $command->execute($this->api, $event->getUpdate());
            $event->setProcessed();

            break;
        }
    }
}
