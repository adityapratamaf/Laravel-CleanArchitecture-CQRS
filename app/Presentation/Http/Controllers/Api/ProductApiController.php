<?php

namespace App\Presentation\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;

use App\Application\Product\DTOs\CreateProductDTO;
use App\Application\Product\DTOs\UpdateProductDTO;

use App\Application\Product\Commands\CreateProduct\CreateProductCommand;
use App\Application\Product\Commands\UpdateProduct\UpdateProductCommand;
use App\Application\Product\Commands\DeleteProduct\DeleteProductCommand;

use App\Application\Product\Queries\GetProductById\GetProductByIdQuery;
use App\Application\Product\Queries\ListProducts\ListProductsQuery;

use App\Presentation\Http\Requests\Product\StoreProductRequest;
use App\Presentation\Http\Requests\Product\UpdateProductRequest;

use App\Support\Helpers\FileUpload;

class ProductApiController
{
    public function index(Request $request, QueryBus $queryBus)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $result = $queryBus->ask(new ListProductsQuery(
            page: $page,
            perPage: $perPage,
            search: is_string($request->query('search')) ? $request->query('search') : null,
            sortBy: is_string($request->query('sort_by')) ? $request->query('sort_by') : 'id',
            sortDir: is_string($request->query('sort_dir')) ? $request->query('sort_dir') : 'desc',
        ));

        $start = (($result->meta['current_page'] - 1) * $result->meta['per_page']) + 1;

        return response()->json([
            'data' => array_map(function($dto) use (&$start) {
                return [
                    'no' => $start++,
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'sku' => $dto->sku,
                    'price' => $dto->price,
                    'stock' => $dto->stock,
                    'description' => $dto->description,
                ];
            }, $result->data),
            'meta' => $result->meta,
        ]);
    }

    public function store(StoreProductRequest $request, CommandBus $commandBus)
    {
        $imagePath = $request->hasFile('image')
            ? FileUpload::storePublic($request->file('image'), 'products')
            : null;

        $dto = new CreateProductDTO(
            name: $request->string('name')->toString(),
            sku: $request->string('sku')->toString(),
            price: (float) $request->input('price'),
            stock: (int) $request->input('stock'),
            description: $request->filled('description') ? (string) $request->input('description') : null,
            image: $imagePath,
        );

        $p = $commandBus->dispatch(new CreateProductCommand($dto));

        return response()->json([
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => $p->price,
            'stock' => $p->stock,
            'description' => $p->description,
            'image' => $p->image,
            'image_url' => $p->imageUrl,
        ], 201);
    }

    public function show(int $id, QueryBus $queryBus)
    {
        $p = $queryBus->ask(new GetProductByIdQuery($id));

        return response()->json([
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => $p->price,
            'stock' => $p->stock,
            'description' => $p->description,
        ]);
    }

    public function update(int $id, UpdateProductRequest $request, CommandBus $commandBus)
    {
        $imagePath = $request->hasFile('image')
            ? FileUpload::storePublic($request->file('image'), 'products')
            : null;

        $dto = new UpdateProductDTO(
            id: $id,
            name: $request->string('name')->toString(),
            sku: $request->string('sku')->toString(),
            price: (float) $request->input('price'),
            stock: (int) $request->input('stock'),
            description: $request->filled('description') ? (string) $request->input('description') : null,
            image: $imagePath,
        );

        $p = $commandBus->dispatch(new UpdateProductCommand($dto));

        return response()->json([
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => $p->price,
            'stock' => $p->stock,
            'description' => $p->description,
            'image' => $p->image,
            'image_url' => $p->imageUrl,
        ]);
    }

    public function destroy(int $id, CommandBus $commandBus)
    {
        $commandBus->dispatch(new DeleteProductCommand($id));

        return response()->json(['message' => 'Product deleted']);
    }
}