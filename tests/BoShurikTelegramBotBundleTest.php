<?php

/*
 * This file is part of the BoShurikTelegramBotBundle.
 *
 * (c) Alexander Borisov <boshurik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BoShurik\TelegramBotBundle\Tests;

use BoShurik\TelegramBotBundle\BoShurikTelegramBotBundle;
use BoShurik\TelegramBotBundle\DependencyInjection\BoShurikTelegramBotExtension;
use PHPUnit\Framework\TestCase;

class BoShurikTelegramBotBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new BoShurikTelegramBotBundle();

        $this->assertInstanceOf(BoShurikTelegramBotExtension::class, $bundle->getContainerExtension());
        $this->assertSame('bo_shurik_telegram_bot', $bundle->getContainerExtension()->getAlias());
    }
}