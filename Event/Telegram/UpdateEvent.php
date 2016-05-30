<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 17:57
 */

namespace BoShurik\TelegramBotBundle\Event\Telegram;

use Symfony\Component\EventDispatcher\Event;
use TelegramBot\Api\Types\Update;

class UpdateEvent extends Event
{
    /**
     * @var Update
     */
    private $update;

    /**
     * @var boolean
     */
    private $processed;

    public function __construct(Update $update)
    {
        $this->update = $update;
        $this->processed = false;
    }

    /**
     * @return Update
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @return void
     */
    public function setProcessed()
    {
        $this->processed = true;
    }
}