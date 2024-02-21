<?php

declare(strict_types=1);

namespace Czernika\FortifyWpUsers\Auth\Providers;

use Illuminate\Auth\DatabaseUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;

class WpDatabaseUserProvider extends DatabaseUserProvider implements UserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        if ($this->hasher->info($hashed = $user->getAuthPassword())['algoName'] === 'unknown') {
            return false;
        }

        return $this->hasher->check($credentials['password'], $hashed);
    }
}
