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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;
use TelegramBot\Api\Types\Update;

final class WebhookEvent extends Event
{
    private ?Response $response;

    public function __construct(private string $bot, private Request $request, private Update $update)
    {
        $this->response = null;
    }

    public function getBot(): string
    {
        return $this->bot;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}
