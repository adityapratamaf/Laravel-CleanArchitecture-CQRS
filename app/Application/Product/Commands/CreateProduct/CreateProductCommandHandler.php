<?php

namespace App\Application\Product\Commands\CreateProduct;

use App\Application\Product\DTOs\ProductDTO;
use App\Domain\Product\Contracts\ProductRepository;
use App\Domain\Product\Entities\Product;

class CreateProductCommandHandler
{
    public function __construct(private ProductRepository $products) {}

    public function handle(CreateProductCommand $command): ProductDTO
    {
        $data = $command->data;

        if ($this->products->findBySku($data->sku)) {
            throw new \DomainException('SKU already exists');
        }

        $entity = new Product(
            id: null,
            name: $data->name,
            sku: $data->sku,
            price: $data->price,
            stock: $data->stock,
            description: $data->description
        );

        $created = $this->products->create($entity);

        return new ProductDTO(
            $created->id,
            $created->name,
            $created->sku,
            $created->price,
            $created->stock,
            $created->description
        );
    }
}