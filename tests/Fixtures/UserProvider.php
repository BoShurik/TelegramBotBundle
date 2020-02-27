<?php

namespace BoShurik\TelegramBotBundle\Tests\Fixtures;

use BoShurik\TelegramBotBundle\Guard\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;
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
