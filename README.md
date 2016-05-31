TelegramBotBundle
===========

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
    class: "%app.telegram.command.help.class%"
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