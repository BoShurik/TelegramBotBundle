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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandRegistry;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;

class CommandListener implements EventSubscriberInterface
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

    /**
     * @param UpdateEvent $event
     */
    public function onUpdate(UpdateEvent $event)
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
