<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Telegram\Command;

use BoShurik\TelegramBotBundle\Tests\Fixtures\AliasCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\FromAbstractCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\ParametersCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\TargetAllCommand;
use BoShurik\TelegramBotBundle\Tests\Fixtures\TargetCallbackCommand;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class AbstractCommandTest extends TestCase
{
    /**
     * @dataProvider abstractCommandProvider
     */
    public function testAbstractCommand(Update $update, bool $expected)
    {
        $command = new FromAbstractCommand();

        $this->assertSame($expected, $command->isApplicable($update));
    }

    public function abstractCommandProvider()
    {
        yield [$this->createMessageUpdate('/bar'), true];
        yield [$this->createMessageUpdate('/bar test'), true];
        yield [$this->createMessageUpdate('/bar_test'), false];
        yield [$this->createMessageUpdate('/foo'), false];
        yield [$this->createCallbackUpdate('/bar'), false];

        yield [$this->createEmptyUpdate(), false];
        yield [$this->createMessageUpdate(null), false];
        yield [$this->createCallbackUpdate(null), false];
    }

    public function testAliasIsApplicable(): void
    {
        $command = new AliasCommand();

        $this->assertTrue($command->isApplicable($this->createMessageUpdate('/alias')));
        $this->assertTrue($command->isApplicable($this->createMessageUpdate('alias')));
        $this->assertTrue($command->isApplicable($this->createMessageUpdate('AliasCommand')));
    }

    /**
     * @dataProvider targetCallbackAbstractCommandProvider
     */
    public function testTargetCallbackAbstractCommand(Update $update, bool $expected)
    {
        $command = new TargetCallbackCommand();

        $this->assertSame($expected, $command->isApplicable($update));
    }

    public function targetCallbackAbstractCommandProvider()
    {
        yield [$this->createMessageUpdate('/bar'), false];
        yield [$this->createMessageUpdate('/foo'), false];
        yield [$this->createCallbackUpdate('/bar'), true];
        yield [$this->createCallbackUpdate('/bar test'), true];
        yield [$this->createCallbackUpdate('/bar_test'), false];
        yield [$this->createCallbackUpdate('/foo'), false];

        yield [$this->createEmptyUpdate(), false];
        yield [$this->createMessageUpdate(null), false];
        yield [$this->createCallbackUpdate(null), false];
    }

    /**
     * @dataProvider targetAllAbstractCommandProvider
     */
    public function testTargetAllAbstractCommand(Update $update, bool $expected)
    {
        $command = new TargetAllCommand();

        $this->assertSame($expected, $command->isApplicable($update));
    }

    public function targetAllAbstractCommandProvider()
    {
        yield [$this->createMessageUpdate('/bar'), true];
        yield [$this->createMessageUpdate('/bar test'), true];
        yield [$this->createMessageUpdate('/bar_test'), false];
        yield [$this->createMessageUpdate('/foo'), false];
        yield [$this->createCallbackUpdate('/bar'), true];
        yield [$this->createCallbackUpdate('/bar test'), true];
        yield [$this->createCallbackUpdate('/bar_test'), false];
        yield [$this->createCallbackUpdate('/foo'), false];

        yield [$this->createEmptyUpdate(), false];
        yield [$this->createMessageUpdate(null), false];
        yield [$this->createCallbackUpdate(null), false];
    }

    /**
     * @dataProvider parametersProvider
     */
    public function testParameters(Update $update, ?string $expected)
    {
        $command = new ParametersCommand(function (?string $actual) use ($expected) {
            $this->assertSame($expected, $actual);
        });

        $command->execute(new BotApi('token'), $update);
    }

    public function parametersProvider()
    {
        yield [$this->createEmptyUpdate(), null];
        yield [$this->createMessageUpdate('/foo'), null];
        yield [$this->createMessageUpdate('/foo '), null];
        yield [$this->createMessageUpdate('/foo  '), ' '];
        yield [$this->createMessageUpdate('/foo 1'), '1'];
        yield [$this->createCallbackUpdate('/foo'), null];
        yield [$this->createCallbackUpdate('/foo '), null];
        yield [$this->createCallbackUpdate('/foo  '), ' '];
        yield [$this->createCallbackUpdate('/foo 1'), '1'];
    }

    /**
     * @return bool|Update
     */
    private function createMessageUpdate(?string $text)
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

    /**
     * @return bool|Update
     */
    private function createCallbackUpdate(?string $data)
    {
        return Update::fromResponse([
            'update_id' => 1,
            'callback_query' => [
                'id' => 2,
                'from' => [
                    'id' => 3,
                    'first_name' => 'firstName',
                ],
                'data' => $data,
            ],
        ]);
    }

    /**
     * @return bool|Update
     */
    private function createEmptyUpdate()
    {
        return Update::fromResponse([
            'update_id' => 1,
        ]);
    }
}
