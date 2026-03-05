<?php

namespace App\Application\Auth\Commands\LoginWeb;

use Illuminate\Support\Facades\Auth;

class LoginWebCommandHandler
{
    public function handle(LoginWebCommand $command): void
    {
        $data = $command->data;

        $ok = Auth::attempt(
            ['email' => $data->email, 'password' => $data->password],
            $data->remember
        );

        if (!$ok) {
            throw new \DomainException('Email atau password salah');
        }

        request()->session()->regenerate();
    }
}