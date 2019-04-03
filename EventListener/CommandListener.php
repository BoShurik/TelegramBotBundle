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

use TelegramBot\Api\BotApi;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandPool;

use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;

class CommandListener
{
    /**
     * @var BotApi
     */
    private $api;
    
    /**
     * @var CommandPool
     */
    private $commandPool;

    public function __construct(BotApi $api, CommandPool $commandPool)
    {
        $this->api = $api;
        $this->commandPool = $commandPool;
    }

    /**
     * @param UpdateEvent $event
     */
    public function onUpdate(UpdateEvent $event)
    {
        foreach ($this->commandPool->getCommands() as $command) {
            if (!$command->isApplicable($event->getUpdate())) {
                continue;
            }

            $command->execute($this->api, $event->getUpdate());
            $event->setProcessed();

            break;
        }
    }
}
