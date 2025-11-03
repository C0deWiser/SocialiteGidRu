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
        // Идентификатор пользователя в Газпром ID, поле sub в ответе
        'openid',
        // ФИО, поля last_name, first_name, patronymic в ответе
        'profile',
        // Никнейм, поле nickname в ответе
        'nickname',
        // Основной email (если есть подтверждённые) или последний неподтверждённый, поле email в ответе
        'email',
        // Признак подтверждения email (bool), поле email_confirmed в ответе
        'email_confirmed',
        // Аватар пользователя, поле avatar в ответе
        'avatar',
        // Обеспечивает получение refresh_token
        'offline_access'
    ];

    /**
     * @see https://docs.auth.gid.ru/docs/oidc/oidc_ACF_Code_request/
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.gid.ru/oauth2/auth', $state);
    }

    /**
     * @see https://docs.auth.gid.ru/docs/gid/backend/outgoing-token-request/
     */
    protected function getTokenUrl()
    {
        return 'https://auth.gid.ru/oauth2/token';
    }

    /**
     * @see https://docs.auth.gid.ru/docs/gid/backend/userinfo-request/
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://auth.gid.ru/api/v1/userinfo', [
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