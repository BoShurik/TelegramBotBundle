<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Telegram\Command;

use BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use BoShurik\TelegramBotBundle\Tests\Fixtures\FromAbstractCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\FromInterfaceCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\PublicCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class HelpCommandTest extends TestCase
{
    public function testHelpCommandOutput(): void
    {
        $commandRegistry = new CommandRegistry();
        $commandRegistry->addCommand(new FromAbstractCommand());
        $commandRegistry->addCommand(new FromInterfaceCommand());
        $commandRegistry->addCommand(new PublicCommand());
        $commandRegistry->addCommand($helpCommand = new HelpCommand($commandRegistry));

        /** @var BotApi|MockObject $api */
        $api = $this->createMock(BotApi::class);
        $api
            ->expects($this->once())
            ->method('sendMessage')
            ->with(
                4,
                "/public - Public command\n/help - Help\n",
                null,
                false,
                null,
                null,
                false
            )
        ;
        $update = Update::fromResponse([
            'update_id' => 1,
            'message' => [
                'message_id' => 2,
                'date' => 3,
                'chat' => [
                    'id' => 4,
                    'type' => 5,
                ],
            ],
        ]);

        $helpCommand->execute($api, $update);
    }

    public function testHelpCommandAliases(): void
    {
        $commandRegistry = new CommandRegistry();
        $commandRegistry->addCommand($helpCommand = new HelpCommand($commandRegistry, 'Help', ['alias']));

        $this->assertSame(['alias'], $helpCommand->getAliases());
    }
}
