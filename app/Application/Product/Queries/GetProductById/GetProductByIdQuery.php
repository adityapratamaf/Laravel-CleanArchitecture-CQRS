<?php

namespace App\Application\Product\Queries\GetProductById;

class GetProductByIdQuery
{
    public function __construct(public readonly int $id) {}
}