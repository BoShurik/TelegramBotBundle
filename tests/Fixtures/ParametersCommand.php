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

class ParametersCommand extends AbstractCommand
{
    /**
     * @var callable
     */
    private $assert;

    public function __construct(callable $assert)
    {
        $this->assert = $assert;
    }

    public function getName()
    {
        return '/foo';
    }

    public function execute(BotApi $api, Update $update)
    {
        \call_user_func($this->assert, $this->getCommandParameters($update));
    }

    protected function getTarget(): int
    {
        return self::TARGET_ALL;
    }
}
