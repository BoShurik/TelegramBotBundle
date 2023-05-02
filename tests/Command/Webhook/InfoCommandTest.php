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
use TelegramBot\Api\Types\WebhookInfo;

class InfoCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::bootKernel();

        $info = new WebhookInfo();
        $info->setUrl('https://google.com');
        $info->setHasCustomCertificate(true);
        $info->setPendingUpdateCount(10);
        $info->setLastErrorDate(1592654050);
        $info->setLastErrorMessage('Oops');
        $info->setMaxConnections(10);
        $info->setAllowedUpdates(['foo', 'bar']);

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->once())
            ->method('getWebhookInfo')
            ->willReturn($info);

        $application = new Application($kernel);

        date_default_timezone_set('UTC');

        $command = $application->find('telegram:webhook:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL            https://google.com', $output);
        $this->assertStringContainsString('Custom Certificate     yes', $output);
        $this->assertStringContainsString('Pending Update Count   10', $output);
        $this->assertStringContainsString('Last Error Date        2020-06-20 11:54:10', $output);
        $this->assertStringContainsString('Last Error Message     Oops', $output);
        $this->assertStringContainsString('Max Connections        10', $output);
        $this->assertStringContainsString('Allowed Updates        foo, bar', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithMultipleBots(): void
    {
        static::$class = MultipleTestKernel::class;

        $kernel = static::bootKernel();

        $info = new WebhookInfo();
        $info->setUrl('https://google.com');
        $info->setHasCustomCertificate(true);
        $info->setPendingUpdateCount(10);
        $info->setLastErrorDate(1592654050);
        $info->setLastErrorMessage('Oops');
        $info->setMaxConnections(10);
        $info->setAllowedUpdates(['foo', 'bar']);

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('getWebhookInfo')
            ->willReturn($info);
        $secondBotApi
            ->expects($this->once())
            ->method('getWebhookInfo')
            ->willReturn($info);

        $application = new Application($kernel);

        date_default_timezone_set('UTC');

        $command = $application->find('telegram:webhook:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringContainsString('Bot "second"', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithMultipleBotsWithSingleBot(): void
    {
        static::$class = MultipleTestKernel::class;

        $kernel = static::bootKernel();

        $info = new WebhookInfo();
        $info->setUrl('https://google.com');
        $info->setHasCustomCertificate(true);
        $info->setPendingUpdateCount(10);
        $info->setLastErrorDate(1592654050);
        $info->setLastErrorMessage('Oops');
        $info->setMaxConnections(10);
        $info->setAllowedUpdates(['foo', 'bar']);

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('getWebhookInfo')
            ->willReturn($info);
        $secondBotApi
            ->expects($this->never())
            ->method('getWebhookInfo');

        $application = new Application($kernel);

        date_default_timezone_set('UTC');

        $command = $application->find('telegram:webhook:info');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'bot' => 'first',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringNotContainsString('Bot "second"', $output);
        $commandTester->assertCommandIsSuccessful();
    }
}
