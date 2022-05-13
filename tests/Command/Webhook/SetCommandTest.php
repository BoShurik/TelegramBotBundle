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

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use TelegramBot\Api\BotApi;

class SetCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url|hostname' => 'https://google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook URL has been set to https://google.com', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithHostname()
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
        $botApi
            ->expects($this->once())
            ->method('setWebhook')
            ->with('https://google.com/_telegram/secret:token/', null);

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url|hostname' => 'google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook URL has been set to https://google.com/_telegram/secret:token/', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithHostnameRouteNotSet()
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('router', $router = $this->createMock(RouterInterface::class));
        $router
            ->expects($this->once())
            ->method('generate')
            ->willThrowException(new RouteNotFoundException());

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
        $botApi
            ->expects($this->never())
            ->method('setWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url|hostname' => 'google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('We could not find the webhook route.', $output);
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testExecuteWithCert()
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
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
            'url|hostname' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/cert.crt',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook URL has been set to https://google.com', $output);
        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteWithBrokenCert()
    {
        $this->expectException(\RuntimeException::class);

        $kernel = static::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url|hostname' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/no.crt',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
