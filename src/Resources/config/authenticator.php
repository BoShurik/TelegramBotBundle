<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BoShurik\TelegramBotBundle\Authenticator\TelegramAuthenticator;
use BoShurik\TelegramBotBundle\Authenticator\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Authenticator\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Authenticator\UserLoaderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TelegramAuthenticator::class)
        ->args([
            service(TelegramLoginValidator::class),
            service(UserLoaderInterface::class),
            service(UserFactoryInterface::class),
            service(UrlGeneratorInterface::class),
            '%boshurik_telegram_bot.guard.guard_route%',
            '%boshurik_telegram_bot.guard.default_target_route%',
            '%boshurik_telegram_bot.guard.login_route%',
        ]);

    $services->set(TelegramLoginValidator::class)
        ->args([
            service('%boshurik_telegram_bot.authenticator.token%'),
        ]);
};
