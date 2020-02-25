<?php

namespace BoShurik\TelegramBotBundle\Tests\Guard;

use BoShurik\TelegramBotBundle\Guard\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Guard\UserLoaderInterface;

interface UserLoaderAndFactoryInterface extends UserLoaderInterface, UserFactoryInterface
{
    // Dummy interface for tests
}
