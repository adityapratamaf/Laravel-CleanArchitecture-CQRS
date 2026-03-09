<?php

namespace App\Application\Product\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $sku,
        public readonly float $price,
        public readonly int $stock,
        public readonly ?string $description,
        public readonly ?string $image,
        public readonly ?string $imageUrl,
    ) {}
}