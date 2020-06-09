<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Guard;

use BoShurik\TelegramBotBundle\Exception\AuthenticationException;
use BoShurik\TelegramBotBundle\Guard\TelegramLoginValidator;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

class TelegramLoginValidatorTest extends TestCase
{
    use PHPMock;

    private const TOKEN = 'TOKEN';

    public function testThrowExceptionOnMissingData(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing');

        $validator->validate([]);
    }

    public function testThrowExceptionOnExpiredData(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('expired');

        $validator->validate([
            'id' => 0,
            'first_name' => 'fake',
            'last_name' => 'user',
            'auth_date' => time() - 3600 - 1,
            'hash' => '',
        ]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testThrowExceptionOnInvalidChecksum(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('checksum');

        // Mock `time()` function
        $time = $this->getFunctionMock('BoShurik\TelegramBotBundle\Guard', 'time');
        $time->expects($this->once())->willReturn(0);

        $validator->validate([
            'id' => 0,
            'first_name' => 'fake',
            'last_name' => 'user',
            'auth_date' => 0,
            'hash' => '201645a6b1d55352f38460a4a8ce683f7975cf92d1c8a3c9cc2bd89290bd7103',
        ]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testValidData(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        // Mock `time()` function
        $time = $this->getFunctionMock('BoShurik\TelegramBotBundle\Guard', 'time');
        $time->expects($this->once())->willReturn(0);

        $validator->validate([
            'id' => 0,
            'first_name' => 'fake',
            'last_name' => 'user',
            'auth_date' => 0,
            'hash' => 'c3a3c6d429c94b91e2ebc6519769cc77c6bd918e54f1433fee55e1912962c206',
        ]);

        $this->assertTrue(true, 'No exception was thrown on valid data');
    }
}
