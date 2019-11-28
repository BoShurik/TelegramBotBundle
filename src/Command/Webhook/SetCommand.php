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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use TelegramBot\Api\BotApi;

class SetCommand extends Command
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
            ->setName('telegram:webhook:set')
            ->addArgument('url', InputArgument::REQUIRED, 'Webhook url')
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

        $certificateFile = null;
        if ($certificate = $input->getArgument('certificate')) {
            if (!is_file($certificate) || !is_readable($certificate)) {
                throw new \RuntimeException(sprintf('Can\'t read certificate file "%s"', $certificate));
            }

            $certificateFile = new \CURLFile($certificate);
        }

        $this->api->setWebhook($url = $input->getArgument('url'), $certificateFile);

        $io->success(sprintf('Webhook url "%s" has been set', $url));

        return 0;
    }
}