<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 18:03
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
            if (!$command->isApplicable($event->getUpdate()->getMessage())) {
                continue;
            }

            $command->execute($this->api, $event->getUpdate()->getMessage());
            $event->setProcessed();

            break;
        }
    }
}