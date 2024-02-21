<?php

declare(strict_types=1);

namespace Czernika\FortifyWpUsers;

use Czernika\FortifyWpUsers\Actions\TryToUpdateWpPassword;
use Czernika\FortifyWpUsers\Hashing\Drivers\WpPassword;
use Czernika\FortifyWpUsers\Hashing\Drivers\WpPasswordContract;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyWpUsersProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WpPasswordContract::class, function () {
            return new WpPassword(new PasswordHash(8, true));
        });
    }

    public function boot()
    {
        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                TryToUpdateWpPassword::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });

    }
}
