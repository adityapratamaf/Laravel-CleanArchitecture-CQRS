<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Contracts\ProductRepository;
use App\Domain\Product\Entities\Product;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;

class EloquentProductRepository implements ProductRepository
{
    public function create(Product $product): Product
    {
        $row = ProductModel::create([
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'stock' => $product->stock,
            'description' => $product->description,
        ]);

        return new Product(
            $row->id,
            $row->name,
            $row->sku,
            (float) $row->price,
            (int) $row->stock,
            $row->description
        );
    }

    public function update(Product $product): Product
    {
        $row = ProductModel::findOrFail($product->id);

        $row->name = $product->name;
        $row->sku = $product->sku;
        $row->price = $product->price;
        $row->stock = $product->stock;
        $row->description = $product->description;
        $row->save();

        return new Product(
            $row->id,
            $row->name,
            $row->sku,
            (float) $row->price,
            (int) $row->stock,
            $row->description
        );
    }

    public function delete(int $id): void
    {
        ProductModel::query()->where('id', $id)->delete();
    }

    public function findById(int $id): ?Product
    {
        $row = ProductModel::find($id);
        if (!$row) return null;

        return new Product(
            $row->id,
            $row->name,
            $row->sku,
            (float) $row->price,
            (int) $row->stock,
            $row->description
        );
    }

    public function findBySku(string $sku): ?Product
    {
        $row = ProductModel::query()->where('sku', $sku)->first();
        if (!$row) return null;

        return new Product(
            $row->id,
            $row->name,
            $row->sku,
            (float) $row->price,
            (int) $row->stock,
            $row->description
        );
    }
}