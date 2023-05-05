# TelegramBotBundle

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
    resource: "@BoShurikTelegramBotBundle/Resources/config/routing.php"
    prefix: /_telegram/%telegram_bot_route_secret%
```

or for multiple bots:
``` yaml
BoShurikTelegramBotBundle:
    resource: "@BoShurikTelegramBotBundle/Resources/config/routing.php"
    prefix: /_telegram/{bot}/%telegram_bot_route_secret%
```

#### Configuration

``` yaml
boshurik_telegram_bot:
    api:
        token: "%telegram_bot_api_token%"
        proxy: "socks5://127.0.0.1:8888"
```

or for multiple bots:
``` yaml
boshurik_telegram_bot:
    api:
        default_bot: first
        bots:
            first: "%first_telegram_bot_api_token%"
            second: "%second_telegram_bot_api_token%"
        proxy: "socks5://127.0.0.1:8888"
```

## Usage

#### API

To get default bot api:
```php
use TelegramBot\Api\BotApi;
public function __construct(private BotApi $api)
```

For multiple bots:

```php

use BoShurik\TelegramBotBundle\Telegram\BotLocator;
use TelegramBot\Api\BotApi;

public function foo(BotLocator $botLocator)
{
    /** @var BotApi $api */
    $api = $botLocator->get('first');
}
```
or use argument with type `TelegramBot\Api\BotApi` and name pattern `/\${name}(Bot|BotApi|Api)?$/`
```php
use TelegramBot\Api\BotApi;
public function __construct(private BotApi $firstBotApi)
```

For more info see [Usage][2] section in [`telegram-bot/api`][1] library

#### Getting updates

``` bash
bin/console telegram:updates
bin/console telegram:updates first
```

For more information see [official documentation][3]

#### Webhook

##### Set

``` bash
bin/console telegram:webhook:set [url-or-hostname] [<path-to-certificate>]
bin/console telegram:webhook:set [url-or-hostname] [<path-to-certificate>] --bot first
```

If `url-or-hostname` is not set command will generate url based on [request context](https://symfony.com/doc/current/routing.html#generating-urls-in-commands)

##### Unset

``` bash
bin/console telegram:webhook:unset
bin/console telegram:webhook:unset first
```

For more information see [official documentation][4]

#### Async command processing

To improve performance, you can leverage [Messenger][7] to process webhooks later via a Messenger transport.

```bash
composer req symfony/messenger
```

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%"

        routing:
            'BoShurik\TelegramBotBundle\Messenger\TelegramMessage': async
```

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

For application with multiple bots you need to pass bot id:
``` yaml
app.telegram.command:
    class: AppBundle\Telegram\Command\SomeCommand
    tags:
        - { name: boshurik_telegram_bot.command, bot: first }
```
If you need to use same command for multiple bots you must add multiple tags for each bot:
``` yaml
app.telegram.command:
    class: AppBundle\Telegram\Command\SomeCommand
    tags:
        - { name: boshurik_telegram_bot.command, bot: first }
        - { name: boshurik_telegram_bot.command, bot: second }
```

There is predefined `\BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand`.
It displays commands which additionally implement `\BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface`

You need to register it:
``` yaml
app.telegram.command.help:
    class: BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand
    arguments:
        - '@boshurik_telegram_bot.command.registry.default'
    tags:
        - { name: boshurik_telegram_bot.command }
```
or for multiple bots:
``` yaml
app.telegram.command.help:
    class: BoShurik\TelegramBotBundle\Telegram\Command\HelpCommand
    arguments:
        - '@boshurik_telegram_bot.command.registry.first'
    tags:
        - { name: boshurik_telegram_bot.command, bot: first }
```

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

## Login with Telegram

This bundle supports login through Telegram Api

If you want to allow your Bot's users to login without requiring them to register again
follow these [instructions](LOGIN_WITH_TELEGRAM.md).

[1]: https://github.com/TelegramBot/Api
[2]: https://github.com/TelegramBot/Api#usage
[3]: https://core.telegram.org/bots/api#getupdates
[4]: https://core.telegram.org/bots/api#setwebhook
[5]: https://github.com/BoShurik/telegram-bot-example
[6]: https://flex.symfony.com
[7]: https://symfony.com/doc/current/messenger.html
