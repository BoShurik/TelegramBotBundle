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
    private const ITEMS = [
        'getUrl',
        'hasCustomCertificate',
        'getPendingUpdateCount',
        'getLastErrorDate',
        'getLastErrorMessage',
        'getMaxConnections',
        'getAllowedUpdates',
    ];

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
            ->setName('telegram:webhook:info')
            ->setDescription('Webhook info')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
            $info->getLastErrorDate() ? date('Y-m-d H:i:s', $info->getLastErrorDate()) : '-',
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
            \is_array($info->getAllowedUpdates()) ? implode(', ', $info->getAllowedUpdates()) : '-',
        ];

        $io->table([
            'name',
            'value',
        ], $values);

        return 0;
    }
}
