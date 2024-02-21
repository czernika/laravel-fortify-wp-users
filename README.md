# Use WordPress passwords in the Laravel application

[![Run tests](https://github.com/czernika/laravel-fortify-wp-users/actions/workflows/tests.yml/badge.svg)](https://github.com/czernika/laravel-fortify-wp-users/actions/workflows/tests.yml)

Allows you to use hashed passwords from WordPress within the Laravel Fortify application when migrating database

> Based on [mikemclin/laravel-wp-password](https://github.com/mikemclin/laravel-wp-password). However this package seems to be abandoned

## The Problem

You need to use old WordPress database data (not the database itself) in a brand new Laravel application. You transferred the data you need (specifically `{prefix}_users`) and expect the user to be able to login using their old passwords within the Laravel [Fortify](https://laravel.com/docs/10.x/fortify) application. However, this is not true as applications use different mechanisms for password hashing (unless you are using some solutions like [roots/wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt)). At best, you will not be able to login, at worst, it will show you some kind of error saying the password is not using the bcrypt/argon algorithm And this is where this package may help

## Solution

We will check almost at the beginning of the authentication pipeline if user exists and the provided hashed password is the same as from the database (assuming it comes from WordPress), update it using standard hashing for Laravel, and pass request to the next action where Fortify itself can check the user. If password is not WordPress one, we will skip this step and move on to the next action 

All we do is check the hash type and if it is a WordPress one, update it

## Installation

```sh
composer require czernika/laravel-fortify-wp-users
```

Done!

## Configuration

Package uses [authentication pipeline](https://laravel.com/docs/10.x/fortify#customizing-the-authentication-pipeline) from Fortify as follows:

```php
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Czernika\FortifyWpUsers\Actions\TryToUpdateWpPassword;

Fortify::authenticateThrough(function (Request $request) {
    return array_filter([
        config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
        TryToUpdateWpPassword::class, // here is where the custom action was added
        Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
        AttemptToAuthenticate::class,
        PrepareAuthenticatedSession::class,
    ]);
});
```

You may override this pipeline in the `FortifyServiceProvider` file. The `TryToUpdateWpPassword` is responsible for updating WordpRess passwords

## Testing

```sh
./vendor/bin/pest
```

## License

Open-source under the [MIT license](LICENSE)
