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

class TelegramMessage
{
    public function __construct(private Update $update)
    {
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }
}
