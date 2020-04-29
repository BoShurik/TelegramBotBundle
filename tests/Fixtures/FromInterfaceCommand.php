<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Fixtures;

use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class FromInterfaceCommand implements CommandInterface
{
    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Update $update)
    {
        return true;
    }
}
