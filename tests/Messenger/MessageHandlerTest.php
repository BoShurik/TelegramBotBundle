<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Messenger;

use BoShurik\TelegramBotBundle\Messenger\MessageHandler;
use BoShurik\TelegramBotBundle\Messenger\TelegramMessage;
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\Types\Update;

class MessageHandlerTest extends TestCase
{
    /**
     * @var Telegram|MockObject
     */
    private $telegram;

    /**
     * @var MessageHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->telegram = $this->createMock(Telegram::class);
        $this->handler = new MessageHandler($this->telegram);
    }

    public function testInvoke()
    {
        $this->telegram
            ->expects($this->once())
            ->method('processUpdate')
            ->with($this->callback(function ($update) {
                if (!$update instanceof Update) {
                    return false;
                }

                return $update->getUpdateId() === 0;
            }))
        ;

        $this->handler->__invoke(new TelegramMessage(Update::fromResponse([
            'update_id' => 0,
        ])));
    }
}
