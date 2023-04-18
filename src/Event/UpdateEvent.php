<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use TelegramBot\Api\Types\Update;

final class UpdateEvent extends Event
{
    private bool $processed;

    public function __construct(private string $bot, private Update $update)
    {
        $this->processed = false;
    }

    public function getBot(): string
    {
        return $this->bot;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(): void
    {
        $this->processed = true;
    }
}
