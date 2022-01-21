<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Fixtures;

use BoShurik\TelegramBotBundle\Authenticator\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Authenticator\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements UserLoaderInterface, UserFactoryInterface
{
    public function createFromTelegram(array $data): UserInterface
    {
    }

    public function loadByTelegramId(string $id): ?UserInterface
    {
    }
}
