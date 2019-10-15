CHANGELOG
=========

3.1.0 (2019-XX-XX)
------------------

* Deprecate `boshurik_telegram_bot.name` as it is not used in the bundle. Inject bot name in your commands if needed.

3.0.0 (2019-04-09)
------------------

* Command system now works with `Update` object instead of `Message` (#14)
* Drop support of php5
* Drop support of symfony/symfony < 3.4
* Change bundle alias from `bo_shurik_telegram_bot` to `boshurik_telegram_bot`
* Split `bin/console telegram:webhook` command to `bin/console telegram:webhook:set` 
and `bin/console telegram:webhook:unset`
* Support autoconfigure for `BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface` interface
* Remove `boshurik_telegram_bot.api` service alias. Use `TelegramBot\Api\BotApi` instead