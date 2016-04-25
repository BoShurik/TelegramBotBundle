<?php
/**
 * User: boshurik
 * Date: 25.04.16
 * Time: 16:36
 */

namespace BoShurik\TelegramBotBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use BoShurik\TelegramBotBundle\Bot\Client;

class UpdatesCommand extends Command
{
    /**
     * @var Client|\TelegramBot\Api\BotApi
     */
    private $client;

    /**
     * @inheritDoc
     */
    public function __construct($name, Client $client)
    {
        parent::__construct($name);

        $this->client = $client;
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
        $updates = $this->client->getUpdates();
        $this->client->handle($updates);
    }
}