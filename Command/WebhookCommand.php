<?php
/**
 * User: boshurik
 * Date: 28.04.16
 * Time: 11:23
 */

namespace BoShurik\TelegramBotBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\BotApi;

class WebhookCommand extends Command
{
    /**
     * @var BotApi
     */
    private $api;

    /**
     * @inheritDoc
     */
    public function __construct($name, BotApi $api)
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
            ->addArgument('url', InputArgument::OPTIONAL, 'Webhook url')
            ->addArgument('certificate', InputArgument::OPTIONAL, 'Path to public key certificate')
            ->setDescription('Set webhook')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$url = $input->getArgument('url')){
            $this->api->setWebhook();

            $io->success('Webhook has been unset');

            return;
        }

        $certificateFile = null;
        if ($certificate = $input->getArgument('certificate')) {
            if (!is_file($certificate) || !is_readable($certificate)) {
                throw new \RuntimeException(sprintf('Can\'t read certificate file "%s"', $certificate));
            }

            $certificateFile = new \CURLFile($certificate);
        }

        $this->api->setWebhook($url, $certificateFile);

        $io->success(sprintf('Webhook url "%s" has been set', $url));
    }
}