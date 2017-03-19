TelegramBotBundle
===========

Telegram bot bundle on top of [`telegram-bot/api`][1] library

## Installation

#### Composer

``` bash
$ composer require boshurik/telegram-bot-bundle
```

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
bo_shurik_telegram_bot:
    api:
        token: "%telegram_bot_api_token%"
    name: "%telegram_bot_name%"
```

## Usage

#### API

```php
    /** @var TelegramBot\Api\BotApi $api */
    $api = $this->container->get('bo_shurik_telegram_bot.api');
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
bin/console telegram:webhook [<url>] [<path-to-certificate>]
```

##### Unset

``` bash
bin/console telegram:webhook
```

For more information see [official documentation][4]

#### Adding commands

Commands must implement `\BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface`

There is `\BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand` you can start with

To register command: add tag `bo_shurik_telegram_bot.command` to service definition
``` yaml
app.telegram.command:
    class: AppBundle/Telegram/Command/SomeCommand
    tags:
        - { name: bo_shurik_telegram_bot.command }
```

There is predefined `\BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand`. You need to register it:
``` yaml
app.telegram.command.help:
    class: BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand
    arguments:
        - "@bo_shurik_telegram_bot.command_pool"
    tags:
        - { name: bo_shurik_telegram_bot.command }
```

#### Events

For more complex application (e.g. conversations) you can listen for `TelegramEvents::UPDATE` event
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

## [Example][5]

[1]: https://github.com/TelegramBot/Api
[2]: https://github.com/TelegramBot/Api#usage
[3]: https://core.telegram.org/bots/api#getupdates
[4]: https://core.telegram.org/bots/api#setwebhook
[5]: https://github.com/BoShurik/telegram-bot-example