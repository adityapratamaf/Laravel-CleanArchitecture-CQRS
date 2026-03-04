<?php

namespace App\Application\User\DTOs;

class PagedUsersDTO
{
    /**
     * @param UserDTO[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly array $meta,
    ) {}
}