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

class WebhookController extends Controller
{
    public function indexAction(Request $request)
    {
        $updates = $this->getApi()->commandsHandler(true);

        return new Response();
    }

    /**
     * @return \Telegram\Bot\Api
     */
    private function getApi()
    {
        return $this->get('bo_shurik_telegram_bot.api');
    }
}