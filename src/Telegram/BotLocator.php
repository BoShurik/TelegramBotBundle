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

use Symfony\Contracts\Service\ServiceProviderInterface;
use TelegramBot\Api\BotApi;

final class BotLocator
{
    public function __construct(private ServiceProviderInterface $locator)
    {
    }

    public function get(string $bot): BotApi
    {
        $api = $this->locator->get($bot);
        if (!$api instanceof BotApi) {
            throw new \RuntimeException(sprintf('Expect "%s", instance of "%s" given', BotApi::class, $api::class));
        }

        return $api;
    }

    /**
     * @return \Generator<string, BotApi>
     */
    public function all(): \Generator
    {
        foreach ($this->locator->getProvidedServices() as $name => $bot) {
            yield $name => $this->get($name);
        }
    }

    public function isSingle(): bool
    {
        return \count($this->locator->getProvidedServices()) === 1;
    }
}
