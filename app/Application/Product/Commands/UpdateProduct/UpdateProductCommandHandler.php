<?php

namespace App\Application\Product\Commands\UpdateProduct;

use App\Application\Product\DTOs\ProductDTO;
use App\Domain\Product\Contracts\ProductRepository;
use App\Support\Helpers\FileUpload;

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

        if ($data->image && $existing->image && $data->image !== $existing->image) {
            FileUpload::deletePublic($existing->image);
        }

        $existing->name = $data->name;
        $existing->sku = $data->sku;
        $existing->price = $data->price;
        $existing->stock = $data->stock;
        $existing->description = $data->description;

        if ($data->image !== null) {
            $existing->image = $data->image;
        }

        $updated = $this->products->update($existing);

        return new ProductDTO(
            $updated->id,
            $updated->name,
            $updated->sku,
            $updated->price,
            $updated->stock,
            $updated->description,
            $updated->image,
            FileUpload::publicUrl($updated->image),
        );
    }
}