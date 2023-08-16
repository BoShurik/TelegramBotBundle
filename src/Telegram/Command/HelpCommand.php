<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

class HelpCommand extends AbstractCommand implements PublicCommandInterface
{
    public function __construct(
        private CommandRegistry $commandRegistry,
        private string $description = 'Help',
        private array $aliases = []
    ) {
    }

    public function execute(BotApi $api, Update $update): void
    {
        $commands = $this->commandRegistry->getCommands();
        /** @var Message $message */
        $message = $update->getMessage();

        $reply = '';
        foreach ($commands as $command) {
            if (!$command instanceof PublicCommandInterface) {
                continue;
            }

            $reply .= sprintf("%s - %s\n", $command->getName(), $command->getDescription());
        }

        $api->sendMessage($message->getChat()->getId(), $reply);
    }

    public function getName(): string
    {
        return '/help';
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
