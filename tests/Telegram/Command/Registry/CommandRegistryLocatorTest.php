<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Telegram\Command\Registry;

use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistryLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Service\ServiceLocatorTrait;
use Symfony\Contracts\Service\ServiceProviderInterface;

class CommandRegistryLocatorTest extends TestCase
{
    public function testGet(): void
    {
        $locator = self::createLocator([
            'one' => $one = new CommandRegistry([]),
            'two' => new CommandRegistry([]),
        ]);

        $this->assertSame($one, $locator->get('one'));
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
    public static function createLocator(array $services): CommandRegistryLocator
    {
        foreach ($services as &$service) {
            $service = fn () => $service;
        }
        unset($service);

        $serviceLocator = new class($services) implements ServiceProviderInterface {
            use ServiceLocatorTrait;
        };

        return new CommandRegistryLocator($serviceLocator);
    }
}
