<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Fixtures;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class FromAbstractCommand extends AbstractCommand
{
    public function getName(): string
    {
        return '/bar';
    }

    public function execute(BotApi $api, Update $update): void
    {
    }
}
