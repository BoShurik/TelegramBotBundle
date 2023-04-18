<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Command;

use BoShurik\TelegramBotBundle\Telegram\Telegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatesCommand extends Command
{
    public function __construct(private Telegram $telegram)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:updates')
            ->setDescription('Get updates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->telegram->processUpdates();

        return Command::SUCCESS;
    }
}
