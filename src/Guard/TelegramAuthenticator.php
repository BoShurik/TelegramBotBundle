<?php

namespace BoShurik\TelegramBotBundle\Guard;

use BoShurik\TelegramBotBundle\Guard\UserProviderInterface as BoShurikUserProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TelegramAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private const REQUIRED_FIELDS = [
        'id',
        'first_name',
        'last_name',
        'auth_date',
        'hash',
    ];

    /**
     * @var string
     */
    private $secret;

    /**
     * @var BoShurikUserProviderInterface
     */
    private $userProvider;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(BoShurikUserProviderInterface $userProvider, UrlGeneratorInterface $urlGenerator, string $telegramBotToken)
    {
        $this->userProvider = $userProvider;
        $this->urlGenerator = $urlGenerator;
        $this->secret = hash('sha256', $telegramBotToken, true);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        $route = $request->attributes->get('_route');
        $data = $request->query->all();

        return $route === '_telegram_login'
            && array_intersect(self::REQUIRED_FIELDS, array_keys($data)) === self::REQUIRED_FIELDS;
    }

    /**
     * {@inheritdoc}
     */
    final public function getCredentials(Request $request)
    {
        $credentials = $request->query->all();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username'] ?? null
        );

        return $credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $this->validate($credentials);

        return $this->userProvider->loadUserByTelegramId($credentials['id']);
    }

    /**
     * Check the data integrity.
     */
    final protected function validate(array $data)
    {
        // Check for data expiration
        // This is optional, but recommended step ;)
        if ((time() - $data['auth_date']) > 3600) {
            throw new CustomUserMessageAuthenticationException('Login data expired');
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $data_check_string = implode("\n", array_map(
            function ($key, $value) { return "$key=$value"; },
            array_keys($data),
            $data
        ));

        // Check for data integrity
        $hmac = hash_hmac('sha256', $data_check_string, $this->secret);
        if ($hmac !== $hash) {
            throw new CustomUserMessageAuthenticationException('Invalid data checksum');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // The check was already done by `validate` method
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('user_profile'));
    }

    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate('login');
    }
}
