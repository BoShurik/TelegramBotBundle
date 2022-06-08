# Login with Telegram


## Setup your bot

First you have to link your domain to the bot,
send the `/setdomain` command to **@Botfather**.


## Configure bundle

Next enable the Telegram Authenticator:
**config/packages/boshurik_telegram_bot.yaml**
```yaml
boshurik_telegram_bot:
  # ...
  authenticator:
    default_target_route: user_profile  # redirect after login success
    guard_route: _telegram_login        # guard route
    login_route: your_login_route       # optional, if login fails user will be redirected there
```

## Generate login widget

In the end you can place a login widget in your login page:
- Open https://core.telegram.org/widgets/login#widget-configuration
- In `Authorization Type` choose `Redirect to URL` option and insert your callback url matching `guard_route` configured before.
  *(**Help**: to discover your guard callback url run `bin/console debug:router _telegram_login`)*
- Copy&Paste the generated snippet code into your `login.html.twig` template.


## Configure Symfony's firewall

**config/packages/security.yaml**
```yaml
security:
  firewalls:
    telegram_bot:
      pattern: ^/_telegram/<a_secret_token>$
      security: false
    main:
      custom_authenticators:
        - BoShurik\TelegramBotBundle\Authenticator\TelegramAuthenticator
      # ...
```

If you are using both classic (username and password form) and Telegram login widget,
you need to add both authenticators to the guard
```yaml
security:
  firewalls:
    # ...
    main:
      custom_authenticators:
        - App\Security\LoginFormAuthenticator
        - BoShurik\TelegramBotBundle\Authenticator\TelegramAuthenticator
      entry_point: App\Security\LoginFormAuthenticator
      # ...
```

## Implement UserProvider

Authenticator should return an `Symfony\Component\Security\Core\User\UserInterface` instance

**UserProvider.php**

```php
<?php

namespace App\Security;

use App\Models\Entity\User;
use BoShurik\TelegramBotBundle\Authenticator\UserFactoryInterface;
use BoShurik\TelegramBotBundle\Authenticator\UserLoaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements UserLoaderInterface, UserFactoryInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadByTelegramId(string $id): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['telegram.id' => $id]);
    }

    public function createFromTelegram(array $data): UserInterface
    {
        $user = new User(
            $data['id'],
            $data['first_name'].' '.$data['last_name'],
            $data['username'] ?? null,
            $data['photo_url'] ?? null
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
```

**Note:** Implementing `UserFactoryInterface` is optional,
  it is required if you want to allow to login all your bot users,
  else the login will be limited to the only already stored in your database.


## Autowiring

**config/services.yaml**
```yaml
services:
  BoShurik\TelegramBotBundle\Authenticator\UserLoaderInterface: '@App\Security\UserProvider'
  BoShurik\TelegramBotBundle\Authenticator\UserFactoryInterface: '@App\Security\UserProvider'
```

## Bot

Set domain in @BotFather for your bot with `/setdomain` command

## Widget

Place widget obtained from [https://core.telegram.org/widgets/login](https://core.telegram.org/widgets/login) on your site
