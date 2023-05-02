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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\BotApi;

final class UnsetCommand extends Command
{
    public function __construct(private BotLocator $botLocator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:webhook:unset')
            ->addArgument('bot', InputArgument::OPTIONAL, 'Bot')
            ->setDescription('Unset webhook')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $bot */
        $bot = $input->getArgument('bot');
        if ($bot) {
            $api = $this->botLocator->get($bot);
            $this->deleteWebhook($io, $bot, $api);
        } else {
            foreach ($this->botLocator->all() as $name => $api) {
                $this->deleteWebhook($io, $name, $api);
            }
        }

        return Command::SUCCESS;
    }

    private function deleteWebhook(SymfonyStyle $io, string $name, BotApi $api): void
    {
        $io->block(sprintf('Bot "%s"', $name));

        $api->deleteWebhook();

        $io->success('Webhook URL has been unset');
    }
}
