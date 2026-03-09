<?php

namespace App\Domain\Product\Entities;

class Product
{
    public function __construct(
        public readonly ?int $id,
        public string $name,
        public string $sku,
        public float $price,
        public int $stock,
        public ?string $description,
        public ?string $image,
    ) {}
}