<?php

namespace App\Application\Product\Commands\UpdateProduct;

use App\Application\Product\DTOs\UpdateProductDTO;

class UpdateProductCommand
{
    public function __construct(public readonly UpdateProductDTO $data) {}
}