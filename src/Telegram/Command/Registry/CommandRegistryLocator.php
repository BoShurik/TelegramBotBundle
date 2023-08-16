<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command\Registry;

use Symfony\Contracts\Service\ServiceProviderInterface;

final class CommandRegistryLocator
{
    public function __construct(private ServiceProviderInterface $locator)
    {
    }

    public function get(string $bot): CommandRegistry
    {
        $registry = $this->locator->get($bot);
        if (!$registry instanceof CommandRegistry) {
            throw new \RuntimeException(sprintf('Expect "%s", instance of "%s" given', CommandRegistry::class, $registry::class));
        }

        return $registry;
    }
}
