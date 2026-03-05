<?php

namespace App\Application\Product\Queries\GetProductById;

use App\Application\Product\DTOs\ProductDTO;
use App\Domain\Product\Contracts\ProductRepository;

class GetProductByIdQueryHandler
{
    public function __construct(private ProductRepository $products) {}

    public function handle(GetProductByIdQuery $query): ProductDTO
    {
        $p = $this->products->findById($query->id);
        if (!$p) {
            throw new \DomainException('Product not found');
        }

        return new ProductDTO($p->id, $p->name, $p->sku, $p->price, $p->stock, $p->description);
    }
}