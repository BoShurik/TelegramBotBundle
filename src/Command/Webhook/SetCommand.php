<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Command\Webhook;

use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TelegramBot\Api\BotApi;

final class SetCommand extends Command
{
    private const MAX_CONNECTIONS = 40;
    private const ALLOWED_UPDATE_TYPES = ['message', 'edited_message', 'channel_post', 'edited_channel_post',
        'inline_query', 'chosen_inline_result', 'callback_query', 'shipping_query', 'pre_checkout_query', 'poll',
        'poll_answer', 'my_chat_member', 'chat_member', 'chat_join_request'];

    public function __construct(private BotLocator $botLocator, private UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:webhook:set')
            ->addArgument('urlOrHostname', InputArgument::OPTIONAL, 'Webhook URL or the host name of your site. if you specify only a host name (without https://), path will be generated for you.')
            ->addArgument('certificate', InputArgument::OPTIONAL, 'Path to public key certificate')
            ->addOption(
                'allowedUpdateType',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Allowed update type. Add option multiple times to add several values.'
            )
            ->addOption('bot', null, InputOption::VALUE_REQUIRED, 'Bot')
            ->setDescription('Set webhook')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $certificateFile = null;
        if ($certificate = $input->getArgument('certificate')) {
            if (!is_file($certificate) || !is_readable($certificate)) {
                throw new \RuntimeException(sprintf('Can\'t read certificate file "%s"', $certificate));
            }

            $certificateFile = new \CURLFile($certificate);
        }

        /** @var string|null $urlOrHostname */
        $urlOrHostname = $input->getArgument('urlOrHostname');
        /** @var string|null $bot */
        $bot = $input->getOption('bot');

        $allowedUpdates = $input->getOption('allowedUpdateType');
        foreach ($allowedUpdates as $update) {
            if (!\in_array($update, self::ALLOWED_UPDATE_TYPES)) {
                $io->error('Incorrect update type: '.$update);

                return self::FAILURE;
            }
        }

        if ($bot) {
            $api = $this->botLocator->get($bot);
            if (!$this->setWebhook($io, $bot, $api, $urlOrHostname, $certificateFile, $allowedUpdates)) {
                return self::FAILURE;
            }
        } else {
            if ($urlOrHostname && str_starts_with($urlOrHostname, 'https://') && !$this->botLocator->isSingle()) {
                $io->error('Can\'t set single url for multiple bots. Pass hostname to generate urls automatically');

                return self::FAILURE;
            }

            foreach ($this->botLocator->all() as $name => $api) {
                if (!$this->setWebhook($io, $name, $api, $urlOrHostname, $certificateFile, $allowedUpdates)) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }

    private function setWebhook(
        SymfonyStyle $io,
        string $name,
        BotApi $api,
        string $urlOrHostname = null,
        \CURLFile $certificateFile = null,
        array $allowedUpdates = null
    ): bool {
        $io->block(sprintf('Bot "%s"', $name));

        if (!$urlOrHostname) {
            $url = $this->urlGenerator->generate('_telegram_bot_webhook', [
                'bot' => $name,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
            if (str_contains($url, '://localhost')) {
                $io->error('Can\'t generate url: request context is not set');

                return false;
            }
        } elseif (!str_starts_with($urlOrHostname, 'https://')) {
            try {
                $url = 'https://'.rtrim($urlOrHostname, '/').$this->urlGenerator->generate('_telegram_bot_webhook', [
                    'bot' => $name,
                ]);
            } catch (RouteNotFoundException $e) {
                $helpUrl = 'https://github.com/BoShurik/TelegramBotBundle#add-routing-for-webhook';
                $message = "We could not find the webhook route. Read on\n<options=bold>%s</>\nhow to add the route or use symfony/flex.";
                $io->block(messages: sprintf($message, $helpUrl), escape: false);

                return false;
            }
        } else {
            $url = $urlOrHostname;
        }

        $api->setWebhook($url, $certificateFile, null, self::MAX_CONNECTIONS, json_encode($allowedUpdates));

        $message = sprintf('Webhook URL has been set to <options=bold>%s</>', $url);
        $io->block($message, 'OK', 'fg=black;bg=green', ' ', true, false);

        return true;
    }
}
