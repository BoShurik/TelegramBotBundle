<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Authenticator;

use BoShurik\TelegramBotBundle\Exception\AuthenticationException;

/**
 * @final
 */
/* final */ class TelegramLoginValidator
{
    private const EXPIRING_TIMEOUT = 3600;

    private const REQUIRED_FIELDS = [
        'id',
        'auth_date',
        'hash',
    ];

    private string $secret;

    public function __construct(string $telegramBotToken)
    {
        $this->secret = hash('sha256', $telegramBotToken, true);
    }

    /**
     * Return TRUE if all required fields are present
     */
    private static function hasAllRequiredFields(array $data): bool
    {
        return array_intersect(self::REQUIRED_FIELDS, array_keys($data)) === self::REQUIRED_FIELDS;
    }

    /**
     * Validate login data
     *
     * @throws AuthenticationException if something goes wrong
     */
    public function validate(array $data): void
    {
        if (!self::hasAllRequiredFields($data)) {
            throw new AuthenticationException('Login data missing');
        }

        // Check for data expiration
        // This is optional, but HIGHLY recommended step 👍
        if ((time() - $data['auth_date']) > self::EXPIRING_TIMEOUT) {
            throw new AuthenticationException('Login data expired');
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Check for data integrity
        $hmac = hash_hmac('sha256', $this->serialize($data), $this->secret);
        if ($hmac !== $hash) {
            throw new AuthenticationException('Invalid data checksum');
        }
    }

    private function serialize(array $data): string
    {
        ksort($data);

        return implode("\n", array_map(
            function ($key, $value) {
                return "$key=$value";
            },
            array_keys($data),
            $data
        ));
    }
}
