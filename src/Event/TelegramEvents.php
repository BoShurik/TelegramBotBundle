<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Event;

final class TelegramEvents
{
    const UPDATE = 'bo_shurik_telegram_bot.update';
    const WEBHOOK = 'bo_shurik_telegram_bot.webhook';
}