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

final class MessageHandler
{
    public function __construct(private Telegram $telegram)
    {
    }

    public function __invoke(TelegramMessage $message)
    {
        $this->telegram->processUpdate($message->getBot(), $message->getUpdate());
    }
}
