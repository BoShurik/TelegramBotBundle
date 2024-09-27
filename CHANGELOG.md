CHANGELOG
=========

6.0.0 (2024-09-27)
------------------

* Allow multiple bots
* Improve the webhook:set command so that it accepts the hostname. The webhook URL will be generated automatically. If url or hostname is not passed then command tries to generate url based on [request context](https://symfony.com/doc/current/routing.html#generating-urls-in-commands)
* Move config from yaml to php files
* Allow to set update type for webhook command
* Allow to set timeout for api instances
* Use symfony/http-client if available
* Add Symfony 7 support

5.0.0 (2022-01-21)
------------------

* Drop php < 8
* Add Symfony 6 support
* Drop Symfony < 5.4
* Remove deprecated `tracker_token` parameter
* Rename `guard` parameter to `authenticator`
* Update authenticator to use new Symfony security system

4.2.0 (2021-09-07)
------------------

* Add support of php8
* Add callback query support for `AbstractCommand`
* Add `AbstractCommand::getCommandParameters()` method

4.1.0 (2020-06-20)
------------------

* Add "Login with Telegram" feature (@bigfoot90)
* Add `telegram:webhook:info` command
* Add `symfony/messenger` support

4.0.0 (2019-11-21)
------------------

* Drop support of `symfony/symfony` < 4.4
* Move `\BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent` to `\BoShurik\TelegramBotBundle\Event\UpdateEvent`
* Move `\BoShurik\TelegramBotBundle\Event\TelegramEvents` to `\BoShurik\TelegramBotBundle\Event\WebhookEvent`
* Removed `\BoShurik\TelegramBotBundle\Event\TelegramEvents`

3.1.0 (2019-10-16)
------------------

* Drop support of php7.0
* Drop support of php7.1
* Add support of php7.4
* Deprecate `boshurik_telegram_bot.name` as it is not used in the bundle. Inject bot name in your commands if needed.

3.0.0 (2019-04-09)
------------------

* Command system now works with `Update` object instead of `Message` (#14)
* Drop support of php5
* Drop support of `symfony/symfony` < 3.4
* Change bundle alias from `bo_shurik_telegram_bot` to `boshurik_telegram_bot`
* Split `bin/console telegram:webhook` command to `bin/console telegram:webhook:set` 
and `bin/console telegram:webhook:unset`
* Support autoconfigure for `BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface` interface
* Remove `boshurik_telegram_bot.api` service alias. Use `TelegramBot\Api\BotApi` instead
