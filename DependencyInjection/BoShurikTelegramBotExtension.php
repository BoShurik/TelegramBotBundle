<?php
/**
 * User: boshurik
 * Date: 01.10.12
 * Time: 23:30
 */

namespace BoShurik\TelegramBotBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('api.yml');
        $loader->load('command.yml');

        $container->setParameter('bo_shurik_telegram_bot.api.token',            $config['api']['token']);
        $container->setParameter('bo_shurik_telegram_bot.api.tracker_token',    $config['api']['tracker_token']);
        $container->setParameter('bo_shurik_telegram_bot.name',                    $config['name']);
    }
}
