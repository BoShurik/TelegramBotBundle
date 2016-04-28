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

use Telegram\Bot\Api;

class WebhookCommand extends Command
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
        $parameters = array(
            'url' => $input->getArgument('url'),
        );

        if ($certificate = $input->getArgument('certificate')) {
            if (!is_file($certificate) || !is_readable($certificate)) {
                throw new \RuntimeException(sprintf('Can\'t read certificate file "%s"', $certificate));
            }

            $parameters['certificate'] = $certificate;
        }

        $response = $this->api->setWebhook($parameters);

        if (!$response['ok']) {
            throw new \RuntimeException(sprintf('%s: %s', $response['error_code'], $response['description']));
        }
    }
}