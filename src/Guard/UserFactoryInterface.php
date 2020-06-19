<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Guard;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserFactoryInterface
{
    /**
     * @param array $data Contains id, first_name, last_name, username, photo_url, auth_date and hash fields.
     */
    public function createFromTelegram(array $data): UserInterface;
}
