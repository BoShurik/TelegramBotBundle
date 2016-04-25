<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 12:46
 */

namespace BoShurik\TelegramBotBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\CommandCompilerPass;

class BoShurikTelegramBotBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CommandCompilerPass());
    }
}