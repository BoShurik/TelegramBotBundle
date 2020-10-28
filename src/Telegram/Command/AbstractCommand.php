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
    protected const TARGET_MESSAGE = 1;
    protected const TARGET_CALLBACK = 2;
    protected const TARGET_ALL = -1;

    /**
     * RegExp for bot commands
     */
    public const REGEXP = '/^([^\s@]+)(@\S+)?\s?(.*)$/';

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
        if ($this->isTargetCallback() && $update->getCallbackQuery()) {
            $data = $update->getCallbackQuery()->getData();
            if ($this->matchCommandName((string) $data, $this->getName())) {
                return true;
            }

            return false;
        }
        if ($this->isTargetMessage() && $update->getMessage()) {
            $message = $update->getMessage();

            if ($this->matchCommandName((string) $message->getText(), $this->getName())) {
                return true;
            }

            foreach ($this->getAliases() as $alias) {
                if ($this->matchCommandName($message->getText(), $alias)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    protected function getTarget(): int
    {
        return self::TARGET_MESSAGE;
    }

    /**
     * @param string $text
     * @param string $name
     *
     * @return bool
     */
    protected function matchCommandName($text, $name)
    {
        preg_match(self::REGEXP, $text, $matches);

        return !empty($matches) && $matches[1] == $name;
    }

    protected function getCommandParameters(Update $update): ?string
    {
        $matches = [];
        if ($this->isTargetCallback() && $update->getCallbackQuery()) {
            preg_match(self::REGEXP, (string) $update->getCallbackQuery()->getData(), $matches);
        }
        if ($this->isTargetMessage() && $update->getMessage()) {
            preg_match(self::REGEXP, (string) $update->getMessage()->getText(), $matches);
        }

        if (empty($matches)) {
            return null;
        }

        return $matches[3] !== '' ? $matches[3] : null;
    }

    private function isTargetMessage(): bool
    {
        return ($this->getTarget() & self::TARGET_MESSAGE) === self::TARGET_MESSAGE;
    }

    private function isTargetCallback(): bool
    {
        return ($this->getTarget() & self::TARGET_CALLBACK) === self::TARGET_CALLBACK;
    }
}
