<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Controller;

use BoShurik\TelegramBotBundle\Controller\WebhookController;
use BoShurik\TelegramBotBundle\Event\WebhookEvent;
use BoShurik\TelegramBotBundle\Messenger\TelegramMessage;
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use TelegramBot\Api\Types\Update;

class WebhookControllerTest extends TestCase
{
    /**
     * @var Telegram|MockObject
     */
    private $telegram;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var MessageBusInterface|MockObject
     */
    private $bus;

    /**
     * @var WebhookController
     */
    private $controller;

    /**
     * @var WebhookController
     */
    private $controllerWithBus;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->telegram = $this->createMock(Telegram::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->controller = new WebhookController($this->telegram, $this->eventDispatcher);
        $this->controllerWithBus = new WebhookController($this->telegram, $this->eventDispatcher, $this->bus);
    }

    public function testEmptyData(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->controller->indexAction(new Request());
    }

    public function testDefaultResponse(): void
    {
        $request = $this->createRequest(json_encode([
            'update_id' => 0,
        ]));

        $this->telegram
            ->expects($this->once())
            ->method('processUpdate')
            ->with($this->callback(function ($update) {
                return $update instanceof Update;
            }))
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($request) {
                if (!$event instanceof WebhookEvent) {
                    return false;
                }
                if ($event->getRequest() !== $request) {
                    return false;
                }
                if (!$event->getUpdate() instanceof Update) {
                    return false;
                }

                return $event->getUpdate()->getUpdateId() === 0;
            }))
            ->willReturnCallback(function (WebhookEvent $event) {
                return $event;
            })
        ;

        $response = $this->controller->indexAction($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testEventResponse(): void
    {
        $request = $this->createRequest(json_encode([
            'update_id' => 0,
        ]));

        $expectedResponse = new Response();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($request) {
                if (!$event instanceof WebhookEvent) {
                    return false;
                }
                if ($event->getRequest() !== $request) {
                    return false;
                }
                if (!$event->getUpdate() instanceof Update) {
                    return false;
                }

                return $event->getUpdate()->getUpdateId() === 0;
            }))
            ->willReturnCallback(function (WebhookEvent $event) use ($expectedResponse) {
                $event->setResponse($expectedResponse);

                return $event;
            })
        ;

        $response = $this->controller->indexAction($request);

        $this->assertSame($expectedResponse, $response);
    }

    public function testBus()
    {
        $request = $this->createRequest(json_encode([
            'update_id' => 0,
        ]));

        $this->telegram
            ->expects($this->never())
            ->method('processUpdate')
        ;
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) {
                if (!$message instanceof TelegramMessage) {
                    return false;
                }

                return $message->getUpdate()->getUpdateId() === 0;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($request) {
                if (!$event instanceof WebhookEvent) {
                    return false;
                }
                if ($event->getRequest() !== $request) {
                    return false;
                }
                if (!$event->getUpdate() instanceof Update) {
                    return false;
                }

                return $event->getUpdate()->getUpdateId() === 0;
            }))
            ->willReturnCallback(function (WebhookEvent $event) {
                return $event;
            });

        $this->controllerWithBus->indexAction($request);
    }

    private function createRequest(string $content): Request
    {
        return new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $content
        );
    }
}
