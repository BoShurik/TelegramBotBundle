<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\DependencyInjection\Compiler;

use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\CommandCompilerPass;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use BoShurik\TelegramBotBundle\Tests\Fixtures\FromAbstractCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\FromInterfaceCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\PublicCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class CommandCompilerPassTest extends TestCase
{
    public function testRegisterCommand(): void
    {
        $container = $this->buildContainer();

        $container
            ->register(FromInterfaceCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;
        $container
            ->register(FromAbstractCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;
        $container
            ->register(PublicCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;

        $container->compile();

        /** @var CommandRegistry $registry */
        $registry = $container->get('boshurik_telegram_bot.command.registry.default');
        $this->assertCount(3, $registry->getCommands());
        $this->assertContainsOnlyInstancesOf(CommandInterface::class, $registry->getCommands());
    }

    public function testWrongInterfaceForTag(): void
    {
        $this->expectException(LogicException::class);

        $container = $this->buildContainer();

        $container
            ->register('service', '\stdClass')
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;

        $container->compile();
    }

    public function testNoCircularException(): void
    {
        $container = $this->buildContainer();

        $container
            ->register(HelpCommand::class)
            ->addArgument(new Reference('boshurik_telegram_bot.command.registry.default'))
            ->addTag(CommandCompilerPass::COMMAND_TAG)
            ->setPublic(true)
        ;

        $container->compile();

        $this->assertInstanceOf(CommandRegistry::class, $container->get('boshurik_telegram_bot.command.registry.default'));
        $this->assertInstanceOf(HelpCommand::class, $container->get(HelpCommand::class));
    }

    public function testUnknownClass(): void
    {
        $this->expectException(LogicException::class);

        $container = $this->buildContainer();

        $container
            ->register('foo')
            ->addTag(CommandCompilerPass::COMMAND_TAG)
        ;

        $container->compile();
    }

    public function testMultipleBotsContainer(): void
    {
        $container = $this->buildMultipleBotsContainer();

        $container
            ->register(FromInterfaceCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG, [
                'bot' => 'first',
            ])
            ->addTag(CommandCompilerPass::COMMAND_TAG, [
                'bot' => 'second',
            ])
        ;
        $container
            ->register(FromAbstractCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG, [
                'bot' => 'first',
            ])
        ;
        $container
            ->register(PublicCommand::class)
            ->addTag(CommandCompilerPass::COMMAND_TAG, [
                'bot' => 'second',
            ])
        ;

        $container->compile();

        /** @var CommandRegistry $firstRegistry */
        $firstRegistry = $container->get('boshurik_telegram_bot.command.registry.first');
        $this->assertCount(2, $firstRegistry->getCommands());
        $this->assertContainsOnlyInstancesOf(CommandInterface::class, $firstRegistry->getCommands());

        [$firstCommand, $secondCommand] = $firstRegistry->getCommands();
        $this->assertInstanceOf(FromInterfaceCommand::class, $firstCommand);
        $this->assertInstanceOf(FromAbstractCommand::class, $secondCommand);

        /** @var CommandRegistry $secondRegistry */
        $secondRegistry = $container->get('boshurik_telegram_bot.command.registry.second');
        $this->assertCount(2, $secondRegistry->getCommands());
        $this->assertContainsOnlyInstancesOf(CommandInterface::class, $secondRegistry->getCommands());

        [$firstCommand, $secondCommand] = $secondRegistry->getCommands();
        $this->assertInstanceOf(FromInterfaceCommand::class, $firstCommand);
        $this->assertInstanceOf(PublicCommand::class, $secondCommand);
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CommandCompilerPass());

        $container
            ->register('boshurik_telegram_bot.command.registry.default', CommandRegistry::class)
            ->setPublic(true)
            ->setArguments([[]])
            ->addTag(CommandCompilerPass::REGISTRY_TAG, [
                'bot' => 'default',
            ])
        ;

        return $container;
    }

    private function buildMultipleBotsContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CommandCompilerPass());

        $container
            ->register('boshurik_telegram_bot.command.registry.first', CommandRegistry::class)
            ->setPublic(true)
            ->setArguments([[]])
            ->addTag(CommandCompilerPass::REGISTRY_TAG, [
                'bot' => 'first',
            ])
        ;

        $container
            ->register('boshurik_telegram_bot.command.registry.second', CommandRegistry::class)
            ->setPublic(true)
            ->setArguments([[]])
            ->addTag(CommandCompilerPass::REGISTRY_TAG, [
                'bot' => 'second',
            ])
        ;

        return $container;
    }
}
