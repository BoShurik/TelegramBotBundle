<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 14:05
 */

namespace BoShurik\TelegramBotBundle\Bot\Command;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class HelpCommand implements CommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(Client $client, Message $message)
    {
        /** @var \BoShurik\TelegramBotBundle\Bot\Client $client */
        $commands = $client->getCommands();

        $reply = '';
        foreach ($commands as $command) {
            $reply .= sprintf("/%s - %s\n", $command->getName(), $command->getDescription());
        }
        $client->sendMessage($message->getChat()->getId(), $reply);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'help';
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return 'Help';
    }
}