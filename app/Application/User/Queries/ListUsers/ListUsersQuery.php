<?php

namespace App\Application\User\Queries\ListUsers;

class ListUsersQuery
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $search = null,
        public readonly string $sortBy = 'id',
        public readonly string $sortDir = 'desc',
    ) {}
}