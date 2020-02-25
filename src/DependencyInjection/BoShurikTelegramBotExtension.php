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
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yaml');

        $container->setParameter('boshurik_telegram_bot.api.token', $config['api']['token']);
        $container->setParameter('boshurik_telegram_bot.api.tracker_token', $config['api']['tracker_token']);
        $container->setParameter('boshurik_telegram_bot.api.proxy', $config['api']['proxy']);

        $container
            ->registerForAutoconfiguration(CommandInterface::class)
            ->addTag(CommandCompilerPass::TAG)
        ;

        if ($config['guard']['enabled']) {
            $this->configureAuthenticator($container, $config['guard']);
        }
    }

    private function configureAuthenticator(ContainerBuilder $container, array $configs)
    {
        $container->autowire(UserLoaderInterface::class);
        $container->autowire(UrlGeneratorInterface::class);
        $container->autowire(TelegramLoginValidator::class);

        $container->addDefinitions([
            TelegramAuthenticator::class => new Definition(TelegramAuthenticator::class, [
                $container->findDefinition(TelegramLoginValidator::class),
                $container->findDefinition(UserLoaderInterface::class),
                $container->findDefinition(UrlGeneratorInterface::class),
                $configs['guard_route'],
                $configs['default_target_route'],
            ])
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'boshurik_telegram_bot';
    }
}
