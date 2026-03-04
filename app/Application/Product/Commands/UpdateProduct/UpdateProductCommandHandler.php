<?php

namespace App\Application\Product\Commands\UpdateProduct;

use App\Application\Product\DTOs\ProductDTO;
use App\Domain\Product\Contracts\ProductRepository;

class UpdateProductCommandHandler
{
    public function __construct(private ProductRepository $products) {}

    public function handle(UpdateProductCommand $command): ProductDTO
    {
        $data = $command->data;

        $existing = $this->products->findById($data->id);
        if (!$existing) {
            throw new \DomainException('Product not found');
        }

        // SKU uniqueness check (optional but recommended)
        $skuOwner = $this->products->findBySku($data->sku);
        if ($skuOwner && $skuOwner->id !== $data->id) {
            throw new \DomainException('SKU already exists');
        }

        $existing->name = $data->name;
        $existing->sku = $data->sku;
        $existing->price = $data->price;
        $existing->stock = $data->stock;
        $existing->description = $data->description;

        $updated = $this->products->update($existing);

        return new ProductDTO(
            $updated->id,
            $updated->name,
            $updated->sku,
            $updated->price,
            $updated->stock,
            $updated->description
        );
    }
}