<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests\Authenticator;

use BoShurik\TelegramBotBundle\Authenticator\TelegramLoginValidator;
use BoShurik\TelegramBotBundle\Exception\AuthenticationException;
use PHPUnit\Framework\TestCase;

class TelegramLoginValidatorTest extends TestCase
{
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

    public function testThrowExceptionOnInvalidChecksum(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('checksum');

        $validator->validate([
            'id' => 0,
            'first_name' => 'fake',
            'last_name' => 'user',
            'auth_date' => time(),
            'hash' => '201645a6b1d55352f38460a4a8ce683f7975cf92d1c8a3c9cc2bd89290bd7103',
        ]);
    }

    public function testValidData(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $validator->validate([
            'id' => 0,
            'first_name' => 'fake',
            'last_name' => 'user',
            'auth_date' => \PHP_INT_MAX,
            'hash' => '30519422ea1be3c44127f25160e32856d1c43a41b61cbe57463ba5b024103be7',
        ]);

        $this->assertTrue(true, 'No exception was thrown on valid data');
    }

    public function testPartiallyPresentValidData(): void
    {
        $validator = new TelegramLoginValidator(self::TOKEN);

        $validator->validate([
            'id' => 0,
            'auth_date' => \PHP_INT_MAX,
            'hash' => '169389480c74ccd2e8950c9e4d40c45858b17937dd00b165310684577e0d58c6',
        ]);

        $this->assertTrue(true, 'No exception was thrown on valid data');
    }
}
