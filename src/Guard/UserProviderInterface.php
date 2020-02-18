<?php

namespace BoShurik\TelegramBotBundle\Guard;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserProviderInterface
{
    public function loadUserByTelegramId(array $credentials): ?UserInterface;
}