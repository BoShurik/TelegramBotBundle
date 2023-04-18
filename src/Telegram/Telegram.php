<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Telegram;

use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\Types\Update;

/**
 * @final
 */
/* final */ class Telegram
{
    public function __construct(
        private BotLocator $botLocator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function processAllUpdates(): void
    {
        foreach ($this->botLocator->all() as $name => $id) {
            $this->processUpdates($name);
        }
    }

    public function processUpdates(string $bot): void
    {
        $api = $this->botLocator->get($bot);

        $updates = $api->getUpdates();

        $lastUpdateId = null;
        foreach ($updates as $update) {
            $lastUpdateId = $update->getUpdateId();
            $this->processUpdate($bot, $update);
        }

        if ($lastUpdateId) {
            $api->getUpdates($lastUpdateId + 1, 1);
        }
    }

    public function processUpdate(string $bot, Update $update): void
    {
        $event = new UpdateEvent($bot, $update);
        $this->eventDispatcher->dispatch($event);
    }
}
