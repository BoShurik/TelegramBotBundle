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
use BoShurik\TelegramBotBundle\Telegram\Telegram;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @var WebhookController
     */
    private $controller;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->telegram = $this->createMock(Telegram::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->controller = new WebhookController($this->telegram, $this->eventDispatcher);
    }

    public function testEmptyData()
    {
        $this->expectException(BadRequestHttpException::class);

        $this->controller->indexAction(new Request());
    }

    public function testDefaultResponse()
    {
        $request = $this->createRequest(json_encode([
            'update_id' => 'update_id',
        ]));

        $this->telegram
            ->expects($this->once())
            ->method('processUpdate')
            ->with($this->callback(function($update){
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

                return $event->getUpdate() instanceof Update;
            }))
            ->willReturn($this->createMock(WebhookEvent::class))
        ;

        $response = $this->controller->indexAction($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testEventResponse()
    {
        $request = $this->createRequest(json_encode([
            'update_id' => 'update_id',
        ]));

        $eventResponse = new Response('custom');

        $event = $this->createMock(WebhookEvent::class);
        $event
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($eventResponse)
        ;

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn($event)
        ;

        $response = $this->controller->indexAction($request);

        $this->assertSame($eventResponse, $response);
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