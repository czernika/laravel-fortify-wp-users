<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

uses()->group('feature.auth');

beforeEach(function () {
    $this->data = [
        'email' => 'admin@admin.com',
        'password' => 'password',
    ];
});

test('user can authenticate with regular Laravel password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', $this->data);

    $this->assertAuthenticatedAs($user);
});

test('user can authenticate with WordPress password', function () {
    $user = User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    $this->post('/login', $this->data);

    $this->assertAuthenticatedAs($user);
});

test('default fortify auth pipeline can be overwritten', function () {
    User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    // Override authentication pipeline
    Fortify::authenticateThrough(function () {
        return [
            new class
            {
                public function handle()
                {
                    abort(500);
                }
            },
        ];
    });

    /** @var TestResponse $response */
    $response = $this->post('/login', $this->data);

    $response->assertStatus(500);
});

test('when package action is disabled user cannot login with WordPress password', function () {
    User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    // Override authentication pipeline to default one
    Fortify::authenticateThrough(function () {
        return array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            // TryToUpdateWpPassword is missing here
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]);
    });

    $this->post('/login', $this->data);

    $this->assertGuest();
});

test('when user passes incorrect password it throws runtime exception error without configuring user providers', function () {
    $this->withoutExceptionHandling();

    User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    $this->post('/login', [
        'email' => 'admin@admin.com',
        'password' => 'passwors', // typo
    ]);
})->throws(RuntimeException::class);

test('when user passes incorrect password it fail to login with wp_eloquent user provider', function () {
    config()->set('auth.providers.wp_eloquent', [
        'driver' => 'wp_eloquent',
        'model' => App\Models\User::class,
    ]);
    config()->set('auth.guards.web.provider', 'wp_eloquent');
    
    User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    $this->post('/login', [
        'email' => 'admin@admin.com',
        'password' => 'passwors', // typo
    ]);

    $this->assertGuest();
});

test('when user passes incorrect password it fail to login with wp_database user provider', function () {
    config()->set('auth.providers.wp_database', [
        'driver' => 'wp_database',
        'table' => 'users',
    ]);
    config()->set('auth.guards.web.provider', 'wp_database');
    
    User::factory()->create([
        'password' => '$P$B4x2yN4GIdtfW/FP5IJ06rl1BUTKhU.', // password
    ]);

    $this->post('/login', [
        'email' => 'admin@admin.com',
        'password' => 'passwors', // typo
    ]);

    $this->assertGuest();
});
