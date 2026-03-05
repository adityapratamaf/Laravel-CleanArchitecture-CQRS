<?php

namespace App\Domain\Product\Contracts;

use App\Domain\Product\Entities\Product;

interface ProductRepository
{
    public function create(Product $product): Product;
    public function update(Product $product): Product;
    public function delete(int $id): void;

    public function findById(int $id): ?Product;
    public function findBySku(string $sku): ?Product;
}