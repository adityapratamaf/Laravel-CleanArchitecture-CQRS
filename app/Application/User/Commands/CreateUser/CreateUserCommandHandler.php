<?php

namespace App\Application\User\Commands\CreateUser;

use App\Application\User\DTOs\UserDTO;
use App\Domain\User\Contracts\UserRepository;
use App\Domain\User\Entities\User;

class CreateUserCommandHandler
{
    public function __construct(private UserRepository $users) {}

    public function handle(CreateUserCommand $command): UserDTO
    {
        $data = $command->data;

        if ($this->users->findByEmail($data->email)) {
            throw new \DomainException('Email already registered');
        }

        $entity = new User(
            id: null,
            name: $data->name,
            email: $data->email,
            passwordHash: password_hash($data->password, PASSWORD_BCRYPT),
        );

        $created = $this->users->create($entity);

        return new UserDTO($created->id, $created->name, $created->email);
    }
}