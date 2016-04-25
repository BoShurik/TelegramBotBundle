<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 16:10
 */

namespace BoShurik\TelegramBotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class WebhookController extends Controller
{
    public function indexAction(Request $request)
    {
        $client = $this->getClient();
        if ($data = BotApi::jsonValidate($request->getContent(), true)) {
            $client->handle(array(
                Update::fromResponse($data),
            ));
        } else {
            throw new BadRequestHttpException('Empty data');
        }

        return new Response();
    }

    /**
     * @return \BoShurik\TelegramBotBundle\Bot\Client|\TelegramBot\Api\BotApi
     */
    private function getClient()
    {
        return $this->get('bo_shurik_telegram_bot.client');
    }
}