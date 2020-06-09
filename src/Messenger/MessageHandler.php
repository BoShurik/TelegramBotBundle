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

use BoShurik\TelegramBotBundle\Telegram\Telegram;

class MessageHandler
{
    /**
     * @var Telegram
     */
    private $telegram;

    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }

    public function __invoke(TelegramMessage $message)
    {
        $this->telegram->processUpdate($message->getUpdate());
    }
}
