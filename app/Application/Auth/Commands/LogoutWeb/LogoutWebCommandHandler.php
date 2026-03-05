<?php

namespace App\Application\Auth\Commands\LogoutWeb;

use Illuminate\Support\Facades\Auth;

class LogoutWebCommandHandler
{
    public function handle(LogoutWebCommand $command): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}