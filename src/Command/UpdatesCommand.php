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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use BoShurik\TelegramBotBundle\Telegram\Telegram;

class UpdatesCommand extends Command
{
    /**
     * @var Telegram
     */
    private $telegram;

    /**
     * @inheritDoc
     */
    public function __construct($name, Telegram $telegram)
    {
        parent::__construct($name);

        $this->telegram = $telegram;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Get updates')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->telegram->processUpdates();
    }
}