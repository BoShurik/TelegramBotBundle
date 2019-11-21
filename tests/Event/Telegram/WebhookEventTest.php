<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Event\Telegram;

use BoShurik\TelegramBotBundle\Event\WebhookEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TelegramBot\Api\Types\Update;

class WebhookEventTest extends TestCase
{
    public function testConstructor()
    {
        $event = new WebhookEvent($request = new Request(), $update = Update::fromResponse(['update_id' => 1]));

        $this->assertSame($request, $event->getRequest());
        $this->assertSame($update, $event->getUpdate());
        $this->assertNull($event->getResponse());
    }

    public function testRequest()
    {
        $event = new WebhookEvent($request = new Request(), $update = Update::fromResponse(['update_id' => 1]));
        $event->setResponse($response = new Response());

        $this->assertSame($response,$event->getResponse());
    }
}