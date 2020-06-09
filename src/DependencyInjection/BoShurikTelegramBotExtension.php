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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BoShurikTelegramBotExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        $container->setParameter('boshurik_telegram_bot.api.token', $config['api']['token']);
        $container->setParameter('boshurik_telegram_bot.api.tracker_token', $config['api']['tracker_token']);
        $container->setParameter('boshurik_telegram_bot.api.proxy', $config['api']['proxy']);

        $container
            ->registerForAutoconfiguration(CommandInterface::class)
            ->addTag(CommandCompilerPass::TAG)
        ;

        if ($config['guard']['enabled']) {
            $loader->load('guard.yaml');

            $container->setParameter('boshurik_telegram_bot.guard.guard_route', $config['guard']['guard_route']);
            $container->setParameter('boshurik_telegram_bot.guard.default_target_route', $config['guard']['default_target_route']);
            $container->setParameter('boshurik_telegram_bot.guard.login_route', $config['guard']['login_route'] ?? null);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'boshurik_telegram_bot';
    }
}
