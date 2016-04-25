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
        new BoShurik\AdminBundle\BoShurikAdminBundle(),
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