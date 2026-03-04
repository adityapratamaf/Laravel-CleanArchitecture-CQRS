<?php

namespace App\Application\User\Queries\GetUserById;

class GetUserByIdQuery
{
    public function __construct(public readonly int $id) {}
}