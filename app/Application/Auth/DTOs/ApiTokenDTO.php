<?php

namespace App\Application\Auth\DTOs;

class ApiTokenDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $token_type = 'Bearer',
    ) {}
}