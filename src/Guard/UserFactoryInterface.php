<?php

namespace BoShurik\TelegramBotBundle\Guard;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserFactoryInterface
{
    public function createFromTelegram(array $data): UserInterface;
}