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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use TelegramBot\Api\BotApi;

/**
 * @runTestsInSeparateProcesses
 */
class SetCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'https://google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithHostname(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/secret:route/', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com/_telegram/secret:route/', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithHostnameRouteNotSet(): void
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('router', $router = $this->createMock(RouterInterface::class));
        $router
            ->expects($this->once())
            ->method('generate')
            ->willThrowException(new RouteNotFoundException());

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->never())
            ->method('setWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('We could not find the webhook route.', $output);
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithCert(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com', $this->callback(function ($certificate) {
                if (!$certificate instanceof \CURLFile) {
                    return false;
                }

                return $certificate->getFilename() === __DIR__.'/../Fixtures/cert.crt';
            }));

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/cert.crt',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithBrokenCert(): void
    {
        $this->expectException(\RuntimeException::class);

        $kernel = static::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/no.crt',
        ]);
    }

    public function testExecuteWithUrlAndMultipleBots(): void
    {
        static::$class = MultipleTestKernel::class;
        $kernel = static::bootKernel();

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->never())
            ->method('setWebhook');
        $secondBotApi
            ->expects($this->never())
            ->method('setWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'https://google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Can\'t set single url for multiple bots', $output);
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithHostnameAndMultipleBots(): void
    {
        static::$class = MultipleTestKernel::class;
        $kernel = static::bootKernel();

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/first/secret:route/', null);
        $secondBotApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/second/secret:route/', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringContainsString('Bot "second"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com/_telegram/first/secret:route/', $output);
        $this->assertStringContainsString('https://google.com/_telegram/second/secret:route/', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithHostnameAndMultipleBotsForSingleBot(): void
    {
        static::$class = MultipleTestKernel::class;
        $kernel = static::bootKernel();

        $firstBotApi = $this->createMock(BotApi::class);
        $secondBotApi = $this->createMock(BotApi::class);

        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.first', $firstBotApi);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.second', $secondBotApi);

        $firstBotApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/first/secret:route/', null);
        $secondBotApi
            ->expects($this->never())
            ->method('setWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'urlOrHostname' => 'google.com',
            '--bot' => 'first',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "first"', $output);
        $this->assertStringNotContainsString('Bot "second"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com/_telegram/first/secret:route/', $output);
        $this->assertStringNotContainsString('https://google.com/_telegram/second/secret:route/', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithEmptyUrlOrHostname(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);
        self::getContainer()->set('test.router.request_context', RequestContext::fromUri('https://google.com'));

        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/secret:route/', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Bot "default"', $output);
        $this->assertStringContainsString('Webhook URL has been set to', $output);
        $this->assertStringContainsString('https://google.com/_telegram/secret:route/', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithEmptyUrlOrHostnameAndEmptyRequestContext(): void
    {
        $kernel = static::bootKernel();

        $botApi = $this->createMock(BotApi::class);
        self::getContainer()->set('test.boshurik_telegram_bot.api.bot.default', $botApi);

        $botApi
            ->expects($this->never())
            ->method('setWebhook')
        ;

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Can\'t generate url: request context is not set', $output);
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }
}
