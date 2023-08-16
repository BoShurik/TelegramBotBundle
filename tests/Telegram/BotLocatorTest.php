<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Telegram;

use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ServiceLocatorTrait;
use Symfony\Contracts\Service\ServiceProviderInterface;
use TelegramBot\Api\BotApi;

class BotLocatorTest extends TestCase
{
    public function testGet(): void
    {
        $locator = self::createLocator([
            'one' => $one = new BotApi('one'),
            'two' => new BotApi('two'),
        ]);

        $this->assertSame($one, $locator->get('one'));
    }

    public function testAll(): void
    {
        $apis = [
            'one' => new BotApi('one'),
            'two' => new BotApi('two'),
        ];

        $locator = self::createLocator($apis);

        $this->assertCount(2, iterator_to_array($locator->all()));
        $this->assertFalse($locator->isSingle());

        foreach ($locator->all() as $name => $api) {
            $this->assertSame($apis[$name], $api);
        }
    }

    public function testIsSingle(): void
    {
        $apis = [
            'one' => new BotApi('one'),
        ];

        $locator = self::createLocator($apis);
        $this->assertTrue($locator->isSingle());
    }

    public function testInvalidLocator(): void
    {
        $apis = [
            'one' => new \stdClass(),
        ];
        $locator = self::createLocator($apis);

        $this->expectException(\RuntimeException::class);

        $locator->get('one');
    }

    /**
     * @param array<string, object> $services
     */
    public static function createLocator(array $services): BotLocator
    {
        foreach ($services as &$service) {
            $service = fn () => $service;
        }
        unset($service);

        $serviceLocator = new class($services) implements ServiceProviderInterface {
            use ServiceLocatorTrait;
        };

        return new BotLocator($serviceLocator);
    }
}
