<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\EventListener;

use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use BoShurik\TelegramBotBundle\EventListener\CommandListener;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class CommandListenerTest extends TestCase
{
    /**
     * @var BotApi|MockObject
     */
    private $api;

    /**
     * @var CommandRegistry|MockObject
     */
    private $commandRegistry;

    /**
     * @var CommandListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->api = $this->createMock(BotApi::class);
        $this->commandRegistry = $this->createMock(CommandRegistry::class);

        $this->listener = new CommandListener($this->api, $this->commandRegistry);
    }

    public function testSubscribedEvents(): void
    {
        $this->assertTrue(isset(CommandListener::getSubscribedEvents()[UpdateEvent::class]));
    }

    public function testNotProcessedWhenNoCommands(): void
    {
        /** @var Update $update */
        $update = $this->createMock(Update::class);

        $this->commandRegistry
            ->expects($this->any())
            ->method('getCommands')
            ->willReturn([])
        ;

        $event = new UpdateEvent($update);

        $this->listener->onUpdate($event);

        $this->assertFalse($event->isProcessed());
    }

    public function testNotProcessedWhenCommandsIsNotApplicable(): void
    {
        /** @var Update $update */
        $update = $this->createMock(Update::class);

        $this->commandRegistry
            ->expects($this->any())
            ->method('getCommands')
            ->willReturn([
                new class() implements CommandInterface {
                    public function execute(BotApi $api, Update $update): void
                    {
                    }

                    public function isApplicable(Update $update): bool
                    {
                        return false;
                    }
                },
            ])
        ;

        $event = new UpdateEvent($update);

        $this->listener->onUpdate($event);

        $this->assertFalse($event->isProcessed());
    }

    public function testProcessed(): void
    {
        /** @var Update $update */
        $update = $this->createMock(Update::class);

        $this->commandRegistry
            ->expects($this->any())
            ->method('getCommands')
            ->willReturn([
                new class() implements CommandInterface {
                    public function execute(BotApi $api, Update $update): void
                    {
                    }

                    public function isApplicable(Update $update): bool
                    {
                        return true;
                    }
                },
            ])
        ;

        $event = new UpdateEvent($update);

        $this->listener->onUpdate($event);

        $this->assertTrue($event->isProcessed());
    }
}
