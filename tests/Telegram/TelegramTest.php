<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Telegram;

use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class TelegramTest extends TestCase
{
    /**
     * @var BotApi|MockObject
     */
    private $api;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var Telegram
     */
    private $telegram;

    protected function setUp(): void
    {
        $this->api = $this->createMock(BotApi::class);
        $botLocator = BotLocatorTest::createLocator([
            'default' => $this->api,
        ]);

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->telegram = new Telegram($botLocator, $this->eventDispatcher);
    }

    public function testProcessUpdate(): void
    {
        /** @var Update $update */
        $update = $this->createMock(Update::class);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($update) {
                if (!$event instanceof UpdateEvent) {
                    return false;
                }
                if ($event->getBot() !== 'default') {
                    return false;
                }

                return $event->getUpdate() === $update;
            }))
            ->willReturnArgument(0)
        ;

        $this->telegram->processUpdate('default', $update);
    }

    public function testProcessNoUpdates(): void
    {
        $this->api
            ->expects($this->once())
            ->method('getUpdates')
            ->willReturn([])
        ;

        $this->telegram->processUpdates('default');
    }

    public function testProcessUpdates(): void
    {
        $this->api
            ->expects($this->exactly(2))
            ->method('getUpdates')
            ->withConsecutive([0, 100, 0], [3 /* 2 + 1 */ , 1, 0])
            ->willReturnOnConsecutiveCalls([
                Update::fromResponse(['update_id' => 1]),
                Update::fromResponse(['update_id' => 2]),
            ], [])
        ;

        $this->telegram->processUpdates('default');
    }

    public function testProcessAllUpdates(): void
    {
        $this->api
            ->expects($this->exactly(2))
            ->method('getUpdates')
            ->withConsecutive([0, 100, 0], [3 /* 2 + 1 */ , 1, 0])
            ->willReturnOnConsecutiveCalls([
                Update::fromResponse(['update_id' => 1]),
                Update::fromResponse(['update_id' => 2]),
            ], [])
        ;

        $this->telegram->processAllUpdates();
    }
}
