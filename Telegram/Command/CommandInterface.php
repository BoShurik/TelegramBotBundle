<?php
/**
 * @author: boshurik, martcor
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

interface CommandInterface
{
    /**
     * @param BotApi $api
     * @param Update $update
     * @return mixed
     */
    public function execute(BotApi $api, Update $update);

    /**
     * @param Update $update
     * @return boolean
     */
    public function isApplicable(Update $update);
}
