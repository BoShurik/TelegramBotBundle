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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('boshurik_telegram_bot');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress all */
        $rootNode
            ->children()
                ->arrayNode('api')->isRequired()
                    ->children()
                        ->scalarNode('token')->isRequired()->end()
                        ->scalarNode('proxy')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('authenticator')->canBeEnabled()
                    ->children()
                        ->scalarNode('login_route')->defaultNull()->cannotBeEmpty()->end()
                        ->scalarNode('default_target_route')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('guard_route')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
