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
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\InvalidJsonException;
use TelegramBot\Api\Types\Update;

class WebhookController
{
    /**
     * @var Telegram
     */
    private $telegram;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(Telegram $telegram, EventDispatcherInterface $eventDispatcher)
    {
        $this->telegram = $telegram;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     * @return Response|null
     * @throws InvalidJsonException
     */
    public function indexAction(Request $request)
    {
        if ($content = $request->getContent()) {
            if ($data = BotApi::jsonValidate($content, true)) {
                $update = Update::fromResponse($data);
                $this->telegram->processUpdate($update);
            }
        }

        if (!isset($update)) {
            throw new BadRequestHttpException('Empty data');
        }

        $event = $this->eventDispatcher->dispatch(new WebhookEvent($request, $update));

        return $event->getResponse() ? $event->getResponse() : new Response();
    }
}