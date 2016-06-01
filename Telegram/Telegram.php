<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 18:45
 */

namespace BoShurik\TelegramBotBundle\Telegram;

use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;
use BoShurik\TelegramBotBundle\Event\TelegramEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Telegram
{
    /**
     * @var BotApi
     */
    private $api;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(BotApi $api, EventDispatcherInterface $eventDispatcher)
    {
        $this->api = $api;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return void
     */
    public function processUpdates()
    {
        $updates = $this->api->getUpdates();

        $lastUpdateId = null;
        foreach ($updates as $update) {
            $lastUpdateId = $update->getUpdateId();
            $this->processUpdate($update);
        }

        if ($lastUpdateId) {
            $this->api->getUpdates($lastUpdateId + 1, 1);
        }
    }

    /**
     * @param Update $update
     */
    public function processUpdate($update)
    {
        $event = new UpdateEvent($update);
        $this->eventDispatcher->dispatch(TelegramEvents::UPDATE, $event);
    }
}