<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 13:59
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use TelegramBot\Api\Types\Message;
use TelegramBot\Api\BotApi;

interface CommandInterface
{
    /**
     * @param BotApi $api
     * @param Message $message
     * @return mixed
     */
    public function execute(BotApi $api, Message $message);

    /**
     * @param Message $message
     * @return boolean
     */
    public function isApplicable(Message $message);
}