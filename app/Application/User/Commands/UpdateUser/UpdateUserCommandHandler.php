<?php

namespace App\Application\User\Commands\UpdateUser;

use App\Application\User\DTOs\UserDTO;
use App\Domain\User\Contracts\UserRepository;

class UpdateUserCommandHandler
{
    public function __construct(private UserRepository $users) {}

    public function handle(UpdateUserCommand $command): UserDTO
    {
        $data = $command->data;

        $existing = $this->users->findById($data->id);
        if (!$existing) {
            throw new \DomainException('User not found');
        }

        // kalau password null, pakai password lama
        $passwordHash = $existing->passwordHash;
        if ($data->password !== null && $data->password !== '') {
            $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
        }

        $existing->name = $data->name;
        $existing->email = $data->email;
        $existing->passwordHash = $passwordHash;

        $updated = $this->users->update($existing);

        return new UserDTO($updated->id, $updated->name, $updated->email);
    }
}