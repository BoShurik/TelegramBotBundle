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
use Symfony\Component\Routing\RouterInterface;
use TelegramBot\Api\BotApi;

final class SetCommand extends Command
{
    public function __construct(private BotLocator $botLocator, private RouterInterface $router)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:webhook:set')
            ->addArgument('url|hostname', InputArgument::REQUIRED, 'Webhook URL or the host name of your site. if you specify only a host name (without https://), path will be generated for you.')
            ->addArgument('certificate', InputArgument::OPTIONAL, 'Path to public key certificate')
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

        $url = $input->getArgument('url|hostname');

        /** @var string|null $bot */
        $bot = $input->getOption('bot');
        if ($bot) {
            $api = $this->botLocator->get($bot);
            if (!$this->setWebhook($io, $bot, $api, $url, $certificateFile)) {
                return self::FAILURE;
            }
        } else {
            if (str_starts_with($url, 'https://') && !$this->botLocator->isSingle()) {
                $io->error('Can\'t set single url for multiple bots. Pass hostname to generate urls automatically');

                return self::FAILURE;
            }

            foreach ($this->botLocator->all() as $name => $api) {
                if (!$this->setWebhook($io, $name, $api, $url, $certificateFile)) {
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
        string $url,
        ?\CURLFile $certificateFile
    ): bool {
        $io->block(sprintf('Bot "%s"', $name));

        if (!str_starts_with($url, 'https://')) {
            try {
                $url = 'https://'.rtrim($url, '/').$this->router->generate('_telegram_bot_webhook', [
                    'bot' => $name,
                ]);
            } catch (RouteNotFoundException $e) {
                $helpUrl = 'https://github.com/BoShurik/TelegramBotBundle#add-routing-for-webhook';
                $message = "We could not find the webhook route. Read on\n<options=bold>%s</>\nhow to add the route or use symfony/flex.";
                $io->block(messages: sprintf($message, $helpUrl), escape: false);

                return false;
            }
        }

        $api->setWebhook($url, $certificateFile);

        $message = sprintf('Webhook URL has been set to <options=bold>%s</>', $url);
        $io->block($message, 'OK', 'fg=black;bg=green', ' ', true, false);

        return true;
    }
}
