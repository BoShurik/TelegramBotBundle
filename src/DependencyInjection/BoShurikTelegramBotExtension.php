<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\DependencyInjection;

use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\CommandCompilerPass;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Http\CurlHttpClient;
use TelegramBot\Api\Http\HttpClientInterface;
use TelegramBot\Api\Http\SymfonyHttpClient;

final class BoShurikTelegramBotExtension extends Extension
{
    private const BOT_API_ID_TEMPLATE = 'boshurik_telegram_bot.api.bot.%s';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.php');

        $container->setParameter('boshurik_telegram_bot.api.proxy', $config['api']['proxy']);
        $container->setParameter('boshurik_telegram_bot.api.timeout', $config['api']['timeout']);

        $defaultBot = $config['api']['default_bot'];

        if (interface_exists(HttpClientInterface::class)) {
            if (interface_exists(SymfonyHttpClientInterface::class)) {
                $httpClient = new Definition(SymfonyHttpClient::class, [
                    new Reference(SymfonyHttpClientInterface::class),
                ]);
            } else {
                $httpClient = new Definition(CurlHttpClient::class);
                $httpClient->addMethodCall('setProxy', [new Parameter('boshurik_telegram_bot.api.proxy')]);
                $httpClient->addMethodCall('setOption', [\CURLOPT_TIMEOUT, new Parameter('boshurik_telegram_bot.api.timeout')]);
            }

            $container->setDefinition(HttpClientInterface::class, $httpClient);
        }

        $bots = [];
        $registries = [];
        foreach ($config['api']['bots'] as $name => $bot) {
            $botId = sprintf(self::BOT_API_ID_TEMPLATE, $name);
            $registryId = sprintf(CommandCompilerPass::REGISTRY_ID_TEMPLATE, $name);

            $container
                ->setDefinition(
                    $botId,
                    new ChildDefinition('boshurik_telegram_bot.api.abstract_bot')
                )
                ->setArguments([
                    '$token' => $bot['token'],
                ])
            ;

            $container
                ->setDefinition(
                    $registryId,
                    new ChildDefinition('boshurik_telegram_bot.command.abstract_registry')
                )
                ->addTag(CommandCompilerPass::REGISTRY_TAG, [
                    'bot' => $name,
                ])
            ;

            $bots[$name] = new Reference($botId);
            $registries[$name] = new Reference($registryId);
            if ($name === $defaultBot) {
                $container->setAlias(BotApi::class, $botId);
            }
            $container->registerAliasForArgument($botId, BotApi::class, $name);
            $container->registerAliasForArgument($botId, BotApi::class, $name.'Bot');
            $container->registerAliasForArgument($botId, BotApi::class, $name.'BotApi');
            $container->registerAliasForArgument($botId, BotApi::class, $name.'Api');
        }

        $container
            ->getDefinition('boshurik_telegram_bot.api.bot_locator')
            ->setArguments([$bots])
        ;
        $container
            ->getDefinition('boshurik_telegram_bot.command.registry_locator')
            ->setArguments([$registries])
        ;

        if ($config['authenticator']['enabled']) {
            $loader->load('authenticator.php');

            $authenticatorBot = $config['authenticator']['bot'] ?? $defaultBot;
            $authenticatorToken = $config['api']['bots'][$authenticatorBot];

            $container->setParameter('boshurik_telegram_bot.authenticator.token', $authenticatorToken);
            $container->setParameter('boshurik_telegram_bot.guard.guard_route', $config['authenticator']['guard_route']);
            $container->setParameter('boshurik_telegram_bot.guard.default_target_route', $config['authenticator']['default_target_route']);
            $container->setParameter('boshurik_telegram_bot.guard.login_route', $config['authenticator']['login_route'] ?? null);
        }

        $container
            ->registerForAutoconfiguration(CommandInterface::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;
    }

    public function getAlias(): string
    {
        return 'boshurik_telegram_bot';
    }
}
