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
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BoShurikTelegramBotExtension extends Extension
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $container->setParameter('boshurik_telegram_bot.api.token', $this->config['api']['token']);
        $container->setParameter('boshurik_telegram_bot.api.tracker_token', $this->config['api']['tracker_token']);
        $container->setParameter('boshurik_telegram_bot.api.proxy', $this->config['api']['proxy']);

        $container
            ->registerForAutoconfiguration(CommandInterface::class)
            ->addTag(CommandCompilerPass::TAG)
        ;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'boshurik_telegram_bot';
    }
}
