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

class UnsetCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::bootKernel();

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
        $botApi
            ->expects($this->once())
            ->method('deleteWebhook');

        $application = new Application($kernel);

        $command = $application->find('telegram:webhook:unset');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook URL has been unset', $output);
        $commandTester->assertCommandIsSuccessful();
    }
}
