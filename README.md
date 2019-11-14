# TelegramBotBundle

[![Build Status](https://travis-ci.com/BoShurik/TelegramBotBundle.svg?branch=master)](https://travis-ci.com/BoShurik/TelegramBotBundle)

Telegram bot bundle on top of [`telegram-bot/api`][1] library

## Examples

See [example project][5]

## Installation

#### Composer

``` bash
$ composer require boshurik/telegram-bot-bundle
```

If you are using [symfony/flex][6] all you need is to set `TELEGRAM_BOT_TOKEN` environment variable

#### Register the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new BoShurik\TelegramBotBundle\BoShurikTelegramBotBundle,
    );
    // ...
}
```

#### Add routing for webhook

``` yaml
BoShurikTelegramBotBundle:
    resource: "@BoShurikTelegramBotBundle/Resources/config/routing.yml"
    prefix: /_telegram/<some-secret>
```

#### Configuration

``` yaml
boshurik_telegram_bot:
    api:
        token: "%telegram_bot_api_token%"
        proxy: "socks5://127.0.0.1:8888"
```

## Usage

#### API

```php
    $api = $this->container->get(TelegramBot\Api\BotApi::class);
```

For more info see [Usage][2] section in [`telegram-bot/api`][1] library

#### Getting updates

``` bash
bin/console telegram:updates
```

For more information see [official documentation][3]

#### Webhook

##### Set

``` bash
bin/console telegram:webhook:set <url> [<path-to-certificate>]
```

##### Unset

``` bash
bin/console telegram:webhook:unset
```

For more information see [official documentation][4]

#### Adding commands

Commands must implement `\BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface`

There is `\BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand` you can start with

To register command: add tag `boshurik_telegram_bot.command` to service definition
``` yaml
app.telegram.command:
    class: AppBundle\Telegram\Command\SomeCommand
    tags:
        - { name: boshurik_telegram_bot.command }
```

If you use `autoconfigure` tag will be added automatically

There is predefined `\BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand`. You need to register it:
``` yaml
app.telegram.command.help:
    class: BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand
    arguments:
        - '@BoShurik\TelegramBotBundle\Telegram\Command\CommandRegistry'
    tags:
        - { name: boshurik_telegram_bot.command }
```
It displays commands which additionally implement `\BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface`

#### Events

For more complex application (e.g. conversations) you can listen for `BoShurik\TelegramBotBundle\Event\UpdateEvent` event
``` php
/**
 * @param UpdateEvent $event
 */
public function onUpdate(UpdateEvent $event)
{
    $update = $event->getUpdate();
    $message = $update->getMessage();
}
```

[1]: https://github.com/TelegramBot/Api
[2]: https://github.com/TelegramBot/Api#usage
[3]: https://core.telegram.org/bots/api#getupdates
[4]: https://core.telegram.org/bots/api#setwebhook
[5]: https://github.com/BoShurik/telegram-bot-example
[6]: https://flex.symfony.com