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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\BotApi;

class UnsetCommand extends Command
{
    /**
     * @var BotApi
     */
    private $api;

    /**
     * @inheritDoc
     */
    public function __construct(BotApi $api)
    {
        parent::__construct(null);

        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('telegram:webhook:unset')
            ->setDescription('Unset webhook')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->api->setWebhook();

        $io->success('Webhook has been unset');

        return 0;
    }
}