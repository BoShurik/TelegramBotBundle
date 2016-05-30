<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 18:03
 */

namespace BoShurik\TelegramBotBundle\EventListener;

use TelegramBot\Api\Client;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandPool;

use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;

class CommandListener
{
    /**
     * @var Client
     */
    private $client;
    
    /**
     * @var CommandPool
     */
    private $commandPool;

    public function __construct(Client $client, CommandPool $commandPool)
    {
        $this->client = $client;
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

            $command->execute($this->client, $event->getUpdate()->getMessage());
            $event->setProcessed();

            break;
        }
    }
}