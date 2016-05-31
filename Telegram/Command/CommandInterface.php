<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 13:59
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\BotApi;

interface CommandInterface
{
    /**
     * @param Client|BotApi $client
     * @param Message $message
     * @return mixed
     */
    public function execute(Client $client, Message $message);

    /**
     * @param Message $message
     * @return boolean
     */
    public function isApplicable(Message $message);
}