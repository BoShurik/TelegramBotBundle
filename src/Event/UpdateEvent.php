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