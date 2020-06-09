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
    /**
     * @var Update
     */
    private $update;

    public function __construct(Update $update)
    {
        $this->update = $update;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }
}
