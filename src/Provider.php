<?php

namespace SocialiteProviders\Gidru;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'GIDRU';

    protected $scopeSeparator = ' ';

    protected $scopes = [
        # Идентификатор пользователя в Газпром ID, поле sub в ответе
        'openid',
    ];

    public static function additionalConfigKeys(): array
    {
        return ['base_url'];
    }

    protected function getBaseUrl(): string
    {
        $base_url = $this->getConfig('base_url', 'https://auth.gid.ru');

        return rtrim($base_url, '/');
    }

    protected function buildUrl(string $path): string
    {
        return $this->getBaseUrl().'/'.ltrim($path, '/');
    }

    public function getScopes(): array
    {
        // Эта область возвращает идентификатор пользователя.
        // Без неё всё лишено смысла.
        // Добавим её безусловно.
        $this->scopes('openid');

        return parent::getScopes();
    }

    /**
     * @see https://docs.auth.gid.ru/docs/oidc/oidc_ACF_Code_request/
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->buildUrl('oauth2/auth'), $state);
    }

    /**
     * @see https://docs.auth.gid.ru/docs/gid/backend/outgoing-token-request/
     */
    protected function getTokenUrl(): string
    {
        return $this->buildUrl('oauth2/token');
    }

    /**
     * @see https://docs.auth.gid.ru/docs/gid/backend/userinfo-request/
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->buildUrl('api/v1/userinfo'), [
            RequestOptions::HEADERS => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        $name = array_filter([
            Arr::get($user, 'last_name'),
            Arr::get($user, 'first_name'),
            Arr::get($user, 'patronymic'),
        ]);

        return (new User)->setRaw($user)->map([
            'id'       => Arr::get($user, 'sub'),
            'nickname' => Arr::get($user, 'nickname'),
            'name'     => Str::of(implode(' ', $name))->squish()->toString(),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => Arr::get($user, 'avatar'),
        ]);
    }
}