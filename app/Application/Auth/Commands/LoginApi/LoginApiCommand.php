<?php

namespace App\Application\Auth\Commands\LoginApi;

use App\Application\Auth\DTOs\LoginDTO;

class LoginApiCommand
{
    public function __construct(
        public readonly LoginDTO $data,
        public readonly string $deviceName = 'api'
    ) {}
}