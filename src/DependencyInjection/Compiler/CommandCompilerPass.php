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

use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class CommandCompilerPass implements CompilerPassInterface
{
    const TAG = 'boshurik_telegram_bot.command';

    use PriorityTaggedServiceTrait;

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(CommandRegistry::class);

        $commands = $this->findAndSortTaggedServices(self::TAG, $container);
        foreach ($commands as $command) {
            $definition = $container->getDefinition($command);
            $class = $definition->getClass();
            $interfaces = class_implements($class);
            if (!isset($interfaces[CommandInterface::class])) {
                throw new LogicException(sprintf(
                    'Can\'t apply tag "%s" to %s class. It must implement %s interface',
                    self::TAG,
                    $class,
                    CommandInterface::class
                ));
            }
        }

        $registry->setArgument(0, $commands);
    }
}