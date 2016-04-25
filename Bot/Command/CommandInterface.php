<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 13:59
 */

namespace BoShurik\TelegramBotBundle\Bot\Command;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

interface CommandInterface
{
    /**
     * @param Client $client
     * @param Message $message
     * @return mixed
     */
    public function execute(Client $client, Message $message);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();
}