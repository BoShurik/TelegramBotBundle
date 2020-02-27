<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle;

use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\CommandCompilerPass;
use BoShurik\TelegramBotBundle\DependencyInjection\Compiler\GuardCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BoShurikTelegramBotBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CommandCompilerPass());
        $container->addCompilerPass(new GuardCompilerPass($this->getContainerExtension()));
    }

    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new BoShurikTelegramBotExtension();
        }

        return $this->extension;
    }
}