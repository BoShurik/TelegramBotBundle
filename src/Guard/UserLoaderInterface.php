<?php

namespace BoShurik\TelegramBotBundle\Guard;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserLoaderInterface
{
    public function loadByTelegramId(string $id): ?UserInterface;
}
