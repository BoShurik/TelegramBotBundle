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

class WebhookEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Update
     */
    private $update;

    /**
     * @var Response|null
     */
    private $response;

    public function __construct(Request $request, Update $update)
    {
        $this->request = $request;
        $this->update = $update;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Update
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @return null|Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param null|Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}