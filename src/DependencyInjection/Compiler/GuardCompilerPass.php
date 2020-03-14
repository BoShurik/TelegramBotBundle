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

use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use BoShurik\TelegramBotBundle\Guard\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Guard\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GuardCompilerPass implements CompilerPassInterface
{
    /**
     * @var BoShurikTelegramBotExtension
     */
    private $extension;

    public function __construct(BoShurikTelegramBotExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $config = $this->extension->getConfig();

        if ($config['guard']['enabled']) {
            $this->configureAuthenticator($container, $config['api']['token'], $config['guard']);
        }
    }

    private function configureAuthenticator(ContainerBuilder $container, string $botToken, array $configs)
    {
        $container->setDefinition(TelegramLoginValidator::class, new Definition(TelegramLoginValidator::class, [
                $botToken,
            ])
        );

        $container->setDefinition(TelegramAuthenticator::class, new Definition(TelegramAuthenticator::class, [
                $container->findDefinition(TelegramLoginValidator::class),
                $container->hasDefinition(UserFactoryInterface::class) ? $container->findDefinition(UserFactoryInterface::class) : null,
                $container->findDefinition(UserLoaderInterface::class),
                $container->findDefinition(UrlGeneratorInterface::class),
                $configs['guard_route'],
                $configs['default_target_route'],
                $configs['login_route'] ?? null,
            ])
        )->setPublic(true);
    }
}