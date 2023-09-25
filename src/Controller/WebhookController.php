<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Controller;

use BoShurik\TelegramBotBundle\Event\WebhookEvent;
use BoShurik\TelegramBotBundle\Messenger\TelegramMessage;
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class WebhookController
{
    public function __construct(
        private Telegram $telegram,
        private EventDispatcherInterface $eventDispatcher,
        private ?MessageBusInterface $bus = null
    ) {
    }

    public function indexAction(Request $request): Response
    {
        if ($content = $request->getContent()) {
            /** @var array $data */
            $data = BotApi::jsonValidate($content, true);
            if ($data) {
                $update = Update::fromResponse($data);
                if ($this->bus === null) {
                    $this->telegram->processUpdate($update);
                } else {
                    $this->bus->dispatch(new TelegramMessage($update));
                }
            }
        }

        if (!isset($update)) {
            throw new BadRequestHttpException('Empty data');
        }

        $event = $this->eventDispatcher->dispatch(new WebhookEvent($request, $update));

        return $event->getResponse() ? $event->getResponse() : new Response();
    }
}
