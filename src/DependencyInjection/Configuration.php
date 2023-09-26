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

final class Configuration implements ConfigurationInterface
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
                    ->beforeNormalization()
                        ->ifTrue(static function ($v) {
                            return \is_array($v) && !\array_key_exists('bots', $v) && !\array_key_exists('bot', $v);
                        })
                        ->then(static function ($v) {
                            // Key that should not be rewritten to the connection config
                            $excludedKeys = ['default_bot' => true, 'proxy' => true, 'timeout' => true];
                            $connection = [];
                            foreach ($v as $key => $value) {
                                if (isset($excludedKeys[$key])) {
                                    continue;
                                }

                                $connection[$key] = $v[$key];
                                unset($v[$key]);
                            }

                            $v['default_bot'] = isset($v['default_bot']) ? (string) $v['default_bot'] : 'default';
                            $v['bots'] = [$v['default_bot'] => $connection];

                            return $v;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(static function ($v) {
                            $defaultBot = $v['default_bot'] ?? null;

                            return !isset($v['bots'][$defaultBot]);
                        })
                        ->thenInvalid('Default bot not found')
                    ->end()
                    ->children()
                        ->scalarNode('default_bot')->isRequired()->end()
                    ->end()
                    ->children()
                        ->scalarNode('proxy')->defaultValue('')->end()
                        ->scalarNode('timeout')->defaultValue(10)->end()
                    ->end()
                    ->children()
                        ->arrayNode('bots')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(static function ($v) {
                                        return ['token' => $v];
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('token')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('authenticator')->canBeEnabled()
                    ->children()
                        ->scalarNode('bot')->defaultNull()->end()
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
