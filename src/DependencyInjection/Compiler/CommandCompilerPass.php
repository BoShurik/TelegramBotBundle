<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CommandCompilerPass implements CompilerPassInterface
{
    const TAG = 'boshurik_telegram_bot.command';

    use PriorityTaggedServiceTrait;

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('boshurik_telegram_bot.command_pool');

        $commands = $this->findAndSortTaggedServices(self::TAG, $container);
        foreach ($commands as $command) {
            $pool->addMethodCall('addCommand', [
                $command,
            ]);
        }
    }
}