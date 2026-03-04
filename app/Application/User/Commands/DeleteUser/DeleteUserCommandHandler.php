<?php

namespace App\Application\User\Commands\DeleteUser;

use App\Domain\User\Contracts\UserRepository;

class DeleteUserCommandHandler
{
    public function __construct(private UserRepository $users) {}

    public function handle(DeleteUserCommand $command): void
    {
        $this->users->delete($command->id);
    }
}