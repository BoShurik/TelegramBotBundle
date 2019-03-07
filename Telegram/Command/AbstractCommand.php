<?php
/**
 * @author: boshurik, martcor
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use TelegramBot\Api\Types\Update;

abstract class AbstractCommand implements CommandInterface
{
    /**
     * RegExp for bot commands
     */
    const REGEXP = '/^([^\s@]+)(@\S+)?\s?(.*)$/';

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Update $update)
    {
        $message = $update->getMessage();

        if (is_null($message) || !strlen($message->getText())) {
            return false;
        }

        if ($this->matchCommandName($message->getText(), $this->getName())) {
            return true;
        }

        foreach ($this->getAliases() as $alias) {
            if ($this->matchCommandName($message->getText(), $alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $text
     * @param string $name
     * @return bool
     */
    protected function matchCommandName($text, $name)
    {
        preg_match(self::REGEXP, $text, $matches);

        return !empty($matches) && $matches[1] == $name;
    }
}
