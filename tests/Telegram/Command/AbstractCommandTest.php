<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Telegram\Command;

use BoShurik\TelegramBotBundle\Fixtures\AliasCommand;
use BoShurik\TelegramBotBundle\Fixtures\FromAbstractCommand;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\Types\Update;

class AbstractCommandTest extends TestCase
{
    public function testIsApplicable()
    {
        $command = new FromAbstractCommand();

        $this->assertTrue($command->isApplicable($this->createUpdate('/bar')));
    }

    public function testNotIsApplicable()
    {
        $command = new FromAbstractCommand();

        $this->assertFalse($command->isApplicable($this->createUpdate('/foo')));
    }

    public function testIsApplicableWithNoMessage()
    {
        $command = new FromAbstractCommand();

        $update = Update::fromResponse([
            'update_id' => 1,
        ]);

        $this->assertFalse($command->isApplicable($update));
    }

    public function testAliasIsApplicable()
    {
        $command = new AliasCommand();

        $this->assertTrue($command->isApplicable($this->createUpdate('/alias')));
        $this->assertTrue($command->isApplicable($this->createUpdate('alias')));
        $this->assertTrue($command->isApplicable($this->createUpdate('AliasCommand')));
    }

    /**
     * @param string $text
     * @return bool|Update
     */
    private function createUpdate($text)
    {
        return Update::fromResponse([
            'update_id' => 1,
            'message' => [
                'message_id' => 2,
                'text' => $text,
                'date' => 3,
                'chat' => [
                    'id' => 4,
                    'type' => 5,
                ],
            ],
        ]);
    }
}
