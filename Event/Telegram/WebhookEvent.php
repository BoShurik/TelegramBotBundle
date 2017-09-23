<?php
/**
 * User: boshurik
 * Date: 24.09.17
 * Time: 2:47
 */

namespace BoShurik\TelegramBotBundle\Event\Telegram;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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