CHANGELOG
=========

3.0.0 (2019-XX-XX)
------------------

* Command system now works with `Update` object instead of `Message` (#14)
* Drop support of php5
* Drop support of symfony/symfony < 3.4
* Change bundle alias from `bo_shurik_telegram_bot` to `boshurik_telegram_bot`
* Split `bin/console telegram:webhook` command to `bin/console telegram:webhook:set` 
and `bin/console telegram:webhook:unset`
* Support autoconfigure for `BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface` interface