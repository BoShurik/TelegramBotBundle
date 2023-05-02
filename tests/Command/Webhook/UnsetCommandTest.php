<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Command\Webhook;

use BoShurik\TelegramBotBundle\Tests\Kernel\Multiple\MultipleTestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TelegramBot\Api\BotApi;

/**
 * @runTestsInSeparateProcesses
 */
class UnsetCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->once())
            ->method('deleteWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:unset');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL has been unset', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithMultipleBots(): void
    {
        static::$class = MultipleTestKernel::class;
        $kernel = static::bootKernel();

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('deleteWebhook');
        $secondBotApi
            ->expects($this->once())
            ->method('deleteWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:unset');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringContainsString('Bot "second"', $output);
        $this->assertStringContainsString('Webhook URL has been unset', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteSingleBot(): void
    {
        static::$class = MultipleTestKernel::class;
        $kernel = static::bootKernel();

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('deleteWebhook');
        $secondBotApi
            ->expects($this->never())
            ->method('deleteWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:unset');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'bot' => 'first',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringNotContainsString('Bot "second"', $output);
        $this->assertStringContainsString('Webhook URL has been unset', $output);
        $commandTester->assertCommandIsSuccessful();
    }
}
