<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Command;

use BoShurik\TelegramBotBundle\Telegram\Telegram;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdatesCommandTest extends KernelTestCase
{
    public function testExecuteAll(): void
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.boshurik_telegram_bot.telegram', $telegram = $this->createMock(Telegram::class));
        $telegram
            ->expects($this->once())
            ->method('processAllUpdates');

        $application = new Application($kernel);

        $command = $application->find('telegram:updates');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteDefault(): void
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.boshurik_telegram_bot.telegram', $telegram = $this->createMock(Telegram::class));
        $telegram
            ->expects($this->once())
            ->method('processUpdates')
            ->with('default')
        ;

        $application = new Application($kernel);

        $command = $application->find('telegram:updates');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'bot' => 'default',
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
}
