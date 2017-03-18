<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 15:45
 */

namespace BoShurik\TelegramBotBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('bo_shurik_telegram_bot.command_pool');

        $commands = $this->findAndSortTaggedServices('bo_shurik_telegram_bot.command', $container);
        foreach ($commands as $command) {
            $pool->addMethodCall('addCommand', array(
                $command,
            ));
        }
    }

    /**
     * From PriorityTaggedServiceTrait as we support symfony >= 2.7
     *
     * @param string $tagName
     * @param ContainerBuilder $container
     * @return array|mixed
     */
    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = array();

        foreach ($container->findTaggedServiceIds($tagName) as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $services[$priority][] = new Reference($serviceId);
            }
        }

        if ($services) {
            krsort($services);
            $services = call_user_func_array('array_merge', $services);
        }

        return $services;
    }
}