<?php

namespace App\Application\User\Commands\DeleteUser;

class DeleteUserCommand
{
    public function __construct(public readonly int $id) {}
}