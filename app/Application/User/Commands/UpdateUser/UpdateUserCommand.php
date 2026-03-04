<?php

namespace App\Application\User\Commands\UpdateUser;

use App\Application\User\DTOs\UpdateUserDTO;

class UpdateUserCommand
{
    public function __construct(public readonly UpdateUserDTO $data) {}
}