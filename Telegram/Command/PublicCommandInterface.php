<?php
/**
 * User: boshurik
 * Date: 30.05.16
 * Time: 18:08
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

interface PublicCommandInterface extends CommandInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();
}