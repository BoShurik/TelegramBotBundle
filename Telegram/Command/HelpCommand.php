<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 14:05
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

class HelpCommand extends AbstractCommand implements PublicCommandInterface
{
    /**
     * @var CommandPool
     */
    private $commandPool;

    public function __construct(CommandPool $commandPool)
    {
        $this->commandPool = $commandPool;
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Message $message)
    {
        $commands = $this->commandPool->getCommands();

        $reply = '';
        foreach ($commands as $command) {
            if (!$command instanceof PublicCommandInterface) {
                continue;
            }

            $reply .= sprintf("%s - %s\n", $command->getName(), $command->getDescription());
        }

        $api->sendMessage($message->getChat()->getId(), $reply);
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
        return array();
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return 'Help';
    }
}