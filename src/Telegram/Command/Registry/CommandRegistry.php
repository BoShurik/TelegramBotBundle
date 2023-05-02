<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command\Registry;

use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;

final class CommandRegistry
{
    /**
     * @var CommandInterface[]
     */
    private array $commands;

    public function __construct()
    {
        $this->commands = [];
    }

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    /**
     * @return CommandInterface[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
