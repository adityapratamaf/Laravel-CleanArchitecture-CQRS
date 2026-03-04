<?php

namespace App\Application\User\Commands\CreateUser;

use App\Application\User\DTOs\CreateUserDTO;

class CreateUserCommand
{
    public function __construct(public readonly CreateUserDTO $data) {}
}