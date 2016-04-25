<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 15:43
 */

namespace BoShurik\TelegramBotBundle\Bot;

use TelegramBot\Api\Client as BaseClient;
use TelegramBot\Api\Types\Message;

use BoShurik\TelegramBotBundle\Bot\Command\CommandInterface;

class Client extends BaseClient
{
    private $commands;

    public function __construct($token, $trackerToken)
    {
        parent::__construct($token, $trackerToken);

        $this->commands = array();
    }

    /**
     * @param CommandInterface $command
     */
    public function addCommand(CommandInterface $command)
    {
        $this->commands[] = $command;

        $client = $this;
        $this->command($command->getName(), function(Message $message) use ($client, $command) {
            return $command->execute($client, $message);
        });
    }

    /**
     * @return CommandInterface[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}