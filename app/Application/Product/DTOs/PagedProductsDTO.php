<?php

namespace App\Application\Product\DTOs;

class PagedProductsDTO
{
    /**
     * @param ProductDTO[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly array $meta,
    ) {}
}