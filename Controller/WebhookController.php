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
        if ($data = BotApi::jsonValidate($request->getContent(), true)) {
            $update = Update::fromResponse($data);
            $this->getTelegram()->processUpdate($update);
        } else {
            throw new BadRequestHttpException('Empty data');
        }

        return new Response();
    }

    /**
     * @return \BoShurik\TelegramBotBundle\Telegram\Telegram
     */
    private function getTelegram()
    {
        return $this->get('bo_shurik_telegram_bot.telegram');
    }
}