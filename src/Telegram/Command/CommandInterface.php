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
use TelegramBot\Api\Types\Update;

interface CommandInterface
{
    /**
     * @return void
     */
    public function execute(BotApi $api, Update $update);

    /**
     * @return bool
     */
    public function isApplicable(Update $update);
}
