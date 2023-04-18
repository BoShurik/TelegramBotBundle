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
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use BoShurik\TelegramBotBundle\Tests\Telegram\BotLocatorTest;
use BoShurik\TelegramBotBundle\Tests\Telegram\Command\Registry\CommandRegistryLocatorTest;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class CommandListenerTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $this->assertTrue(isset(CommandListener::getSubscribedEvents()[UpdateEvent::class]));
    }

    public function testNotProcessedWhenNoCommands(): void
    {
        $update = Update::fromResponse(['update_id' => 0]);
        $event = new UpdateEvent('default', $update);

        $listener = $this->createListener([]);
        $listener->onUpdate($event);

        $this->assertFalse($event->isProcessed());
    }

    public function testNotProcessedWhenCommandsIsNotApplicable(): void
    {
        $update = Update::fromResponse(['update_id' => 0]);
        $event = new UpdateEvent('default', $update);

        $listener = $this->createListener([
            new class() implements CommandInterface {
                public function execute(BotApi $api, Update $update): void
                {
                }

                public function isApplicable(Update $update): bool
                {
                    return false;
                }
            },
        ]);

        $listener->onUpdate($event);

        $this->assertFalse($event->isProcessed());
    }

    public function testProcessed(): void
    {
        $update = Update::fromResponse(['update_id' => 0]);
        $event = new UpdateEvent('default', $update);

        $listener = $this->createListener([
            new class() implements CommandInterface {
                public function execute(BotApi $api, Update $update): void
                {
                }

                public function isApplicable(Update $update): bool
                {
                    return true;
                }
            },
        ]);

        $listener->onUpdate($event);

        $this->assertTrue($event->isProcessed());
    }

    /**
     * @param CommandInterface[] $commands
     */
    private function createListener(array $commands): CommandListener
    {
        $botLocator = BotLocatorTest::createLocator([
            'default' => $this->createMock(BotApi::class),
        ]);

        $commandRegistry = new CommandRegistry();
        foreach ($commands as $command) {
            $commandRegistry->addCommand($command);
        }

        $registryLocator = CommandRegistryLocatorTest::createLocator([
            'default' => $commandRegistry,
        ]);

        return new CommandListener($botLocator, $registryLocator);
    }
}
