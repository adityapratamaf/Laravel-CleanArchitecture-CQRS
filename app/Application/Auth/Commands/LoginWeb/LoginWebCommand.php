<?php

namespace App\Application\Auth\Commands\LoginWeb;

use App\Application\Auth\DTOs\LoginDTO;

class LoginWebCommand
{
    public function __construct(public readonly LoginDTO $data) {}
}