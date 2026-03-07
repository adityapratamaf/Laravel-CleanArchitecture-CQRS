<?php

namespace App\Application\Auth\Commands\LogoutApi;

class LogoutApiCommandHandler
{
    public function handle(LogoutApiCommand $command): void
    {
        $user = request()->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }
    }
}