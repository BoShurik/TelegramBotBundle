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
    /**
     * @var BotApi
     */
    private $api;

    /**
     * @var CommandRegistry
     */
    private $commandRegistry;

    public static function getSubscribedEvents()
    {
        return [
            UpdateEvent::class => 'onUpdate',
        ];
    }

    public function __construct(BotApi $api, CommandRegistry $commandRegistry)
    {
        $this->api = $api;
        $this->commandRegistry = $commandRegistry;
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
