<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\DependencyInjection;

use BoShurik\TelegramBotBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider configurationDataProvider
     */
    public function testConfiguration(array $configs, ?array $expected)
    {
        if ($expected === null) {
            $this->expectException(InvalidConfigurationException::class);
        }



        $processor = new Processor();
        $result = $processor->processConfiguration(new Configuration(), [$configs]);

        if ($expected !== null) {
            $this->assertSame($expected, $result);
        }
    }

    public function configurationDataProvider()
    {
        yield [[], null];
        yield [[
            'api' => [
                'token' => 'your secret token',
            ],
        ], [
            'api' => [
                'token' => 'your secret token',
                'tracker_token' => null,
                'proxy' => '',
            ],
            'guard' => [
                'enabled' => false,
                'login_route' => null,
            ],
        ]];
        yield [[
            'api' => [
                'token' => 'your secret token',
                'tracker_token' => 'tracker_token',
                'proxy' => 'proxy',
            ],
        ], [
            'api' => [
                'token' => 'your secret token',
                'tracker_token' => 'tracker_token',
                'proxy' => 'proxy',
            ],
            'guard' => [
                'enabled' => false,
                'login_route' => null,
            ],
        ]];
        yield [[
            'api' => [
                'token' => 'your secret token',
            ],
            'guard' => [
                'guard_route' => 'guard_route',
                'default_target_route' => 'default_target_route',
            ],
        ], [
            'api' => [
                'token' => 'your secret token',
                'tracker_token' => null,
                'proxy' => '',
            ],
            'guard' => [
                'guard_route' => 'guard_route',
                'default_target_route' => 'default_target_route',
                'enabled' => true,
                'login_route' => null,
            ],
        ]];
        yield [[
            'api' => [
                'token' => 'your secret token',
            ],
            'guard' => [
                'login_route' => 'login_route',
                'default_target_route' => 'default_target_route',
                'guard_route' => 'guard_route',
            ],
        ], [
            'api' => [
                'token' => 'your secret token',
                'tracker_token' => null,
                'proxy' => '',
            ],
            'guard' => [
                'login_route' => 'login_route',
                'default_target_route' => 'default_target_route',
                'guard_route' => 'guard_route',
                'enabled' => true,
            ],
        ]];
    }
}
