<?php

declare(strict_types=1);

namespace Czernika\FortifyWpUsers\Actions;

use Czernika\FortifyWpUsers\Hashing\Drivers\WpPasswordContract;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

class TryToUpdateWpPassword
{
    public function __construct(
        protected StatefulGuard $guard,
        protected WpPasswordContract $wpPassword,
    ) {

    }

    public function handle($request, $next)
    {
        $model = $this->guard->getProvider()->getModel();
        $user = $model::firstWhere(Fortify::username(), $request->{Fortify::username()});

        if ($user && $this->wpPassword->check($request->password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return $next($request);
    }
}
