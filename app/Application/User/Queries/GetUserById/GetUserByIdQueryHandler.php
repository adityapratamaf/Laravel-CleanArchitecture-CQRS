<?php

namespace App\Application\User\Queries\GetUserById;

use App\Application\User\DTOs\UserDTO;
use App\Domain\User\Contracts\UserRepository;

class GetUserByIdQueryHandler
{
    public function __construct(private UserRepository $users) {}

    public function handle(GetUserByIdQuery $query): UserDTO
    {
        $user = $this->users->findById($query->id);
        if (!$user) {
            throw new \DomainException('User not found');
        }

        return new UserDTO(
            $user->id, 
            $user->name, 
            $user->email
            );
    }
}