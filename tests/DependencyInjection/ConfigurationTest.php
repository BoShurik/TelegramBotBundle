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
    public function testConfiguration(array $configs, ?array $expected): void
    {
        if ($expected === null) {
            $this->expectException(InvalidConfigurationException::class);
        }

        $processor = new Processor();
        $result = $processor->processConfiguration(new Configuration(), [$configs]);

        if ($expected !== null) {
            $this->assertEqualsCanonicalizing($expected, $result);
        }
    }

    public function configurationDataProvider(): iterable
    {
        yield 'Empty configuration' => [[], null];

        yield 'Simple configuration' => [[
            'api' => [
                'token' => 'your secret token',
            ],
        ], [
            'api' => [
                'default_bot' => 'default',
                'bots' => [
                    'default' => [
                        'token' => 'your secret token',
                    ],
                ],
                'proxy' => '',
            ],
            'authenticator' => [
                'enabled' => false,
                'bot' => null,
                'login_route' => null,
            ],
        ]];

        yield 'Simple configuration with proxy' => [[
            'api' => [
                'token' => 'your secret token',
                'proxy' => 'proxy',
            ],
        ], [
            'api' => [
                'proxy' => 'proxy',
                'default_bot' => 'default',
                'bots' => [
                    'default' => [
                        'token' => 'your secret token',
                    ],
                ],
            ],
            'authenticator' => [
                'enabled' => false,
                'bot' => null,
                'login_route' => null,
            ],
        ]];

        yield 'Simple configuration with authenticator #1' => [[
            'api' => [
                'token' => 'your secret token',
            ],
            'authenticator' => [
                'guard_route' => 'guard_route',
                'default_target_route' => 'default_target_route',
            ],
        ], [
            'api' => [
                'default_bot' => 'default',
                'bots' => [
                    'default' => [
                        'token' => 'your secret token',
                    ],
                ],
                'proxy' => '',
            ],
            'authenticator' => [
                'bot' => null,
                'guard_route' => 'guard_route',
                'default_target_route' => 'default_target_route',
                'enabled' => true,
                'login_route' => null,
            ],
        ]];

        yield 'Simple configuration with authenticator #2' => [[
            'api' => [
                'token' => 'your secret token',
            ],
            'authenticator' => [
                'login_route' => 'login_route',
                'default_target_route' => 'default_target_route',
                'guard_route' => 'guard_route',
            ],
        ], [
            'api' => [
                'default_bot' => 'default',
                'bots' => [
                    'default' => [
                        'token' => 'your secret token',
                    ],
                ],
                'proxy' => '',
            ],
            'authenticator' => [
                'bot' => null,
                'login_route' => 'login_route',
                'default_target_route' => 'default_target_route',
                'guard_route' => 'guard_route',
                'enabled' => true,
            ],
        ]];

        yield 'Multiple bots configuration' => [[
            'api' => [
                'default_bot' => 'first',
                'bots' => [
                    'first' => 'first secret token',
                    'second' => 'second secret token',
                ],
            ],
        ], [
            'api' => [
                'default_bot' => 'first',
                'bots' => [
                    'first' => [
                        'token' => 'first secret token',
                    ],
                    'second' => [
                        'token' => 'second secret token',
                    ],
                ],
                'proxy' => '',
            ],
            'authenticator' => [
                'enabled' => false,
                'bot' => null,
                'login_route' => null,
            ],
        ]];

        yield 'Multiple bots configuration without default bot property' => [[
            'api' => [
                'bots' => [
                    'first' => 'first secret token',
                    'second' => 'second secret token',
                ],
            ],
        ], null];

        yield 'Multiple bots configuration with wrong default bot property' => [[
            'api' => [
                'default_bot' => 'default',
                'bots' => [
                    'first' => 'first secret token',
                    'second' => 'second secret token',
                ],
            ],
        ], null];
    }
}
