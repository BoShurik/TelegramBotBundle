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

use Telegram\Bot\Api;

class UpdatesCommand extends Command
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @inheritDoc
     */
    public function __construct($name, Api $api)
    {
        parent::__construct($name);

        $this->api = $api;
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
        $updates = $this->api->commandsHandler(false);
    }
}