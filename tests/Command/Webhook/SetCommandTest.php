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
use Symfony\Component\Console\Tester\CommandTester;
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
            'url' => 'https://google.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook url "https://google.com" has been set', $output);
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
            'url' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/cert.crt',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook url "https://google.com" has been set', $output);
    }

    public function testExecuteWithBrokenCert()
    {
        $this->expectException(\RuntimeException::class);

        $kernel = static::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'url' => 'https://google.com',
            'certificate' => __DIR__.'/../Fixtures/no.crt',
        ]);
    }
}
