<?php

namespace App\Application\Product\Commands\CreateProduct;

use App\Application\Product\DTOs\CreateProductDTO;

class CreateProductCommand
{
    public function __construct(public readonly CreateProductDTO $data) {}
}