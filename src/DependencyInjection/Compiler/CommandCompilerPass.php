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
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

final class CommandCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;
    public const COMMAND_TAG = 'boshurik_telegram_bot.command';
    public const REGISTRY_TAG = 'boshurik_telegram_bot.command.registry';

    public const REGISTRY_ID_TEMPLATE = 'boshurik_telegram_bot.command.registry.%s';

    public function process(ContainerBuilder $container): void
    {
        $commands = [];

        foreach ($this->findAndSortTaggedServices(new TaggedIteratorArgument(self::COMMAND_TAG), $container) as $command) {
            $definition = $container->getDefinition((string) $command);
            if (!$class = $definition->getClass()) {
                throw new LogicException(sprintf('Unknown class for service "%s"', (string) $command));
            }
            $interfaces = class_implements($class);
            if (!isset($interfaces[CommandInterface::class])) {
                throw new LogicException(sprintf('Can\'t apply tag "%s" to %s class. It must implement %s interface', self::COMMAND_TAG, $class, CommandInterface::class));
            }

            $tags = $definition->getTag(self::COMMAND_TAG);
            foreach ($tags as $tag) {
                $bot = $tag['bot'] ?? 'default';

                $commands[$bot][] = $command;
            }
        }

        foreach ($container->findTaggedServiceIds(self::REGISTRY_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                $bot = $tag['bot'];

                if (isset($commands[$bot])) {
                    $definition = $container->findDefinition($id);
                    foreach ($commands[$bot] as $command) {
                        $definition->addMethodCall('addCommand', [$command]);
                    }
                }
            }
        }
    }
}
