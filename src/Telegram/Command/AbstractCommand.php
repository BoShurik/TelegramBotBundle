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

    abstract public function getName(): string;

    public function getAliases(): array
    {
        return [];
    }

    public function isApplicable(Update $update): bool
    {
        $callbackQuery = $update->getCallbackQuery();
        if ($this->isTargetCallback() && $callbackQuery) {
            $data = $callbackQuery->getData();
            if ($this->matchCommandName((string) $data, $this->getName())) {
                return true;
            }

            return false;
        }
        $message = $update->getMessage();
        if ($this->isTargetMessage() && $message) {
            if ($this->matchCommandName((string) $message->getText(), $this->getName())) {
                return true;
            }

            foreach ($this->getAliases() as $alias) {
                if ($this->matchCommandName((string) $message->getText(), $alias)) {
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

    protected function matchCommandName(string $text, string $name): bool
    {
        preg_match(self::REGEXP, $text, $matches);

        return !empty($matches) && $matches[1] == $name;
    }

    protected function getCommandParameters(Update $update): ?string
    {
        $matches = [];
        $callbackQuery = $update->getCallbackQuery();
        if ($this->isTargetCallback() && $callbackQuery) {
            preg_match(self::REGEXP, (string) $callbackQuery->getData(), $matches);
        }
        $message = $update->getMessage();
        if ($this->isTargetMessage() && $message) {
            preg_match(self::REGEXP, (string) $message->getText(), $matches);
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
