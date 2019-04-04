<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        return [];
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
