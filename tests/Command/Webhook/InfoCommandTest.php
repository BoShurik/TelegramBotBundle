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
use TelegramBot\Api\Types\WebhookInfo;

class InfoCommandTest extends KernelTestCase
{
    public function testExecute()
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

        self::getContainer()->set('test.'.BotApi::class, $botApi = $this->createMock(BotApi::class));
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

        $this->assertStringContainsString('Webhook URL            https://google.com', $output);
        $this->assertStringContainsString('custom certificate     yes', $output);
        $this->assertStringContainsString('pending update count   10', $output);
        $this->assertStringContainsString('last error date        2020-06-20 11:54:10', $output);
        $this->assertStringContainsString('last error message     Oops', $output);
        $this->assertStringContainsString('max connections        10', $output);
        $this->assertStringContainsString('allowed updates        foo, bar', $output);
        $commandTester->assertCommandIsSuccessful();
    }
}
