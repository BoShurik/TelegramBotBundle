<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 17:58
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;


class CommandPool
{
    /**
     * @var CommandInterface[]
     */
    private $commands;

    /**
     * @param CommandInterface $command
     */
    public function addCommand(CommandInterface $command)
    {
        $this->commands[] = $command;
    }

    /**
     * @return CommandInterface[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}