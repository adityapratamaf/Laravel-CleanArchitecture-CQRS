<?php

namespace App\Application\Product\Commands\DeleteProduct;

class DeleteProductCommand
{
    public function __construct(public readonly int $id) {}
}