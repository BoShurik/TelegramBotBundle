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

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

class HelpCommand extends AbstractCommand implements PublicCommandInterface
{
    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $aliases;

    public function __construct(CommandPool $commandPool, $description = 'Help', $aliases = array())
    {
        $this->commandPool = $commandPool;
        $this->description = $description;
        $this->aliases = $aliases;
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
        $commands = $this->commandPool->getCommands();

        $reply = '';
        foreach ($commands as $command) {
            if (!$command instanceof PublicCommandInterface) {
                continue;
            }

            $reply .= sprintf("%s - %s\n", $command->getName(), $command->getDescription());
        }

        $api->sendMessage($update->getMessage()->getChat()->getId(), $reply);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return '/help';
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->description;
    }
}
