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

class InfoCommand extends Command
{
    public function __construct(private BotApi $api)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:webhook:info')
            ->setDescription('Webhook info')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $info = $this->api->getWebhookInfo();

        $values = [];
        $values[] = [
            'url',
            $info->getUrl(),
        ];
        $values[] = [
            'custom certificate',
            $info->hasCustomCertificate() ? 'yes' : 'no',
        ];
        $values[] = [
            'pending update count',
            $info->getPendingUpdateCount(),
        ];
        $values[] = [
            'last error date',
            $info->getLastErrorDate() ? date('Y-m-d H:i:s', (int) $info->getLastErrorDate()) : '-',
        ];
        $values[] = [
            'last error message',
            $info->getLastErrorMessage() ?? '-',
        ];
        $values[] = [
            'max connections',
            $info->getMaxConnections(),
        ];
        $values[] = [
            'allowed updates',
            \is_array($info->getAllowedUpdates()) ? implode(', ', (array) $info->getAllowedUpdates()) : '-',
        ];

        $io->table([
            'name',
            'value',
        ], $values);

        return 0;
    }
}
