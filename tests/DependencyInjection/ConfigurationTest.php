<?php

namespace BoShurik\TelegramBotBundle\DependencyInjection;

use BoShurik\TelegramBotBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testMinimalConfiguration(): void
    {
        $this->assertConfigurationIsValid(
            [
                [] // no values at all
            ]
        );
    }

    public function testMinimalApiConfiguration(): void
    {
        $this->assertConfigurationIsValid(
            [[
                'api' => [
                    'token' => 'your secret token'
                ]
            ]]
        );
    }

    public function testMinimalGuardConfiguration(): void
    {
        $this->assertConfigurationIsValid(
            [[
                'guard' => [
                    'default_target_route' => 'default_target_route',
                ]
            ]]
        );
    }

    public function testFullGuardConfiguration(): void
    {
        $this->assertConfigurationIsValid(
            [[
                'guard' => [
                    'login_route' => 'login_route',
                    'default_target_route' => 'default_target_route',
                    'guard_route' => 'guard_route'
                ]
            ]]
        );
    }
}
