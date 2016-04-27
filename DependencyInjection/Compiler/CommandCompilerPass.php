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
        $pool = $container->getDefinition('bo_shurik_telegram_bot.api');

        foreach ($container->findTaggedServiceIds('bo_shurik_telegram_bot.command') as $id => $tags) {
            foreach ($tags as $tag) {
                $pool->addMethodCall('addCommand', array(
                    new Reference($id),
                ));
            }
        }
    }
}