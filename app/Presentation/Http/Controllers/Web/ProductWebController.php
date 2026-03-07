<?php

namespace App\Presentation\Http\Controllers\Web;

use DomainException;
use Illuminate\Http\Request;
use App\Application\Shared\Bus\QueryBus;
use App\Application\Shared\Bus\CommandBus;

use App\Application\Product\DTOs\CreateProductDTO;
use App\Application\Product\DTOs\UpdateProductDTO;

use App\Application\Product\Commands\CreateProduct\CreateProductCommand;
use App\Application\Product\Commands\UpdateProduct\UpdateProductCommand;
use App\Application\Product\Commands\DeleteProduct\DeleteProductCommand;

use App\Application\Product\Queries\GetProductById\GetProductByIdQuery;
use App\Application\Product\Queries\ListProducts\ListProductsQuery;

use App\Presentation\Http\Requests\Product\StoreProductRequest;
use App\Presentation\Http\Requests\Product\UpdateProductRequest;

use App\Support\Helpers\PaginationLinks;

class ProductWebController
{
    public function index(Request $request, QueryBus $queryBus)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);
        $sortBy = is_string($request->query('sort_by')) ? $request->query('sort_by') : 'id';
        $sortDir = is_string($request->query('sort_dir')) ? $request->query('sort_dir') : 'desc';

        $result = $queryBus->ask(new ListProductsQuery(
            page: $page,
            perPage: $perPage,
            search: is_string($request->query('search')) ? $request->query('search') : null,
            sortBy: $sortBy,
            sortDir: $sortDir
        ));

        $paginationLinks = PaginationLinks::build(
            basePath: '/products',
            query: [
                'search' => is_string($request->query('search')) ? $request->query('search') : null,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
            currentPage: $result->meta['current_page'],
            lastPage: $result->meta['last_page'],
        );

        return view('products.index', [
            'products' => $result->data,
            'meta' => $result->meta,
            'paginationLinks' => $paginationLinks,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request, CommandBus $commandBus)
    {
        try {
            $dto = new CreateProductDTO(
                name: $request->string('name')->toString(),
                sku: $request->string('sku')->toString(),
                price: (float) $request->input('price'),
                stock: (int) $request->input('stock'),
                description: $request->filled('description') ? (string) $request->input('description') : null,
            );

            $commandBus->dispatch(new CreateProductCommand($dto));

            return redirect('/products')->with('success', 'Product created');
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function show(int $id, QueryBus $queryBus)
    {
        try {
            $product = $queryBus->ask(new GetProductByIdQuery($id));
            return view('products.show', compact('product'));
        } catch (DomainException $e) {
            return redirect('/products')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect('/products')->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function edit(int $id, QueryBus $queryBus)
    {
        try {
            $product = $queryBus->ask(new GetProductByIdQuery($id));
            return view('products.edit', compact('product'));
        } catch (DomainException $e) {
            return redirect('/products')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect('/products')->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function update(int $id, UpdateProductRequest $request, CommandBus $commandBus)
    {
        try {
            $dto = new UpdateProductDTO(
                id: $id,
                name: $request->string('name')->toString(),
                sku: $request->string('sku')->toString(),
                price: (float) $request->input('price'),
                stock: (int) $request->input('stock'),
                description: $request->filled('description') ? (string) $request->input('description') : null,
            );

            $commandBus->dispatch(new UpdateProductCommand($dto));

            return redirect('/products/'.$id)->with('success', 'Product updated');
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function destroy(int $id, CommandBus $commandBus)
    {
        try {
            $commandBus->dispatch(new DeleteProductCommand($id));
            return redirect('/products')->with('success', 'Product deleted');
        } catch (DomainException $e) {
            return redirect('/products')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect('/products')->with('error', 'Terjadi kesalahan pada server');
        }
    }
}