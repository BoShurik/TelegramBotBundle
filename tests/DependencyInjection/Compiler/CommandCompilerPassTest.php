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
use BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand;
use BoShurik\TelegramBotBundle\Fixtures\FromAbstractCommand;
use BoShurik\TelegramBotBundle\Fixtures\FromInterfaceCommand;
use BoShurik\TelegramBotBundle\Fixtures\PublicCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class CommandCompilerPassTest extends TestCase
{
    public function testRegisterCommand()
    {
        $container = $container = $this->buildContainer();

        $container
            ->register(FromInterfaceCommand::class)
            ->addTag(CommandCompilerPass::TAG)
        ;
        $container
            ->register(FromAbstractCommand::class)
            ->addTag(CommandCompilerPass::TAG)
        ;
        $container
            ->register(PublicCommand::class)
            ->addTag(CommandCompilerPass::TAG)
        ;

        $container->compile();

        /** @var CommandRegistry $registry */
        $registry = $container->get(CommandRegistry::class);
        $this->assertCount(3, $registry->getCommands());
        $this->assertContainsOnlyInstancesOf(CommandInterface::class, $registry->getCommands());
    }

    public function testWrongInterfaceForTag()
    {
        $this->expectException(LogicException::class);

        $container = $this->buildContainer();

        $container
            ->register('service', '\stdClass')
            ->addTag(CommandCompilerPass::TAG)
        ;

        $container->compile();
    }

    public function testNoCircularException()
    {
        $container = $this->buildContainer();

        $container
            ->register(HelpCommand::class)
            ->addArgument(new Reference(CommandRegistry::class))
            ->addTag(CommandCompilerPass::TAG)
            ->setPublic(true)
        ;

        $container->compile();

        $this->assertInstanceOf(CommandRegistry::class, $container->get(CommandRegistry::class));
        $this->assertInstanceOf(HelpCommand::class, $container->get(HelpCommand::class));
    }

    /**
     * @return ContainerBuilder
     */
    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new CommandCompilerPass());

        $container
            ->register(CommandRegistry::class)
            ->setPublic(true)
        ;

        return $container;
    }
}
