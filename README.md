# Gid.ru

Add repository to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "github",
      "url": "https://github.com/C0deWiser/SocialiteGidRu"
    }
  ]
}
```

```bash
composer require codewiser/gidru
```

## Register an application

Add new application at [gid.ru](https://auth.gid.ru).

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'gidru' => [    
  'base_url'      => env('GIDRU_BASE_URL'), // optional
  'client_id'     => env('GIDRU_CLIENT_ID'),  
  'client_secret' => env('GIDRU_CLIENT_SECRET'),  
  'redirect'      => env('GIDRU_REDIRECT_URI') 
],
```

### Add provider event listener

#### Laravel 11+

In Laravel 11, the default `EventServiceProvider` provider was removed. Instead, add the listener using the `listen` method on the `Event` facade, in your `AppServiceProvider` `boot` method.

* Note: You do not need to add anything for the built-in socialite providers unless you override them with your own providers.

```php
Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
    $event->extendSocialite('gidru', \SocialiteProviders\Gidru\Provider::class);
});
```
<details>
<summary>
Laravel 10 or below
</summary>
Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\Gidru\GidruExtendSocialite::class.'@handle',
    ],
];
```
</details>

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('gidru')->redirect();
```

### Returned User fields

- ``id``
- ``nickname``
- ``name``
- ``email``
- ``avatar``

### Reference

- [Gid.ru API Reference](https://docs.auth.gid.ru/docs/gid/backend/userinfo-request/)