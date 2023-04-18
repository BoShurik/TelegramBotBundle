<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Messenger;

use TelegramBot\Api\Types\Update;

final class TelegramMessage
{
    public function __construct(private string $bot, private Update $update)
    {
    }

    public function getBot(): string
    {
        return $this->bot;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }
}
